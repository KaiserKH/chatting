<?php

declare(strict_types=1);

function render_page(string $name, array $data = []): void
{
    extract($data);
    include base_path('src/partials/header.php');
    include base_path('src/pages/'.$name.'.php');
    include base_path('src/partials/footer.php');
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function route_dispatch(string $method, string $path): void
{
    if ($path === '/logout' && $method === 'POST') {
        verify_csrf_or_fail();
        logout_user();
        redirect_to('/login');
    }

    if ($path === '/login' && $method === 'GET') {
        render_page('login');
        return;
    }

    if ($path === '/login' && $method === 'POST') {
        verify_csrf_or_fail();

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'login_'.$ip;
        if (!empty($_SESSION['rate'][$key]) && $_SESSION['rate'][$key]['count'] >= 10 && (time() - $_SESSION['rate'][$key]['start']) < 60) {
            flash('error', 'Too many attempts. Wait one minute.');
            redirect_to('/login');
        }

        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $remember = !empty($_POST['remember']);

        if (authenticate($login, $password, $remember)) {
            unset($_SESSION['rate'][$key]);
            redirect_to('/chat');
        }

        if (empty($_SESSION['rate'][$key]) || (time() - $_SESSION['rate'][$key]['start']) >= 60) {
            $_SESSION['rate'][$key] = ['count' => 0, 'start' => time()];
        }
        $_SESSION['rate'][$key]['count']++;

        flash('error', 'Invalid credentials or account banned.');
        redirect_to('/login');
    }

    if ($path === '/register' && $method === 'GET') {
        if (!setting_bool('registration_enabled', true)) {
            http_response_code(403);
            exit('Registration disabled by admin.');
        }
        render_page('register');
        return;
    }

    if ($path === '/register' && $method === 'POST') {
        verify_csrf_or_fail();
        if (!setting_bool('registration_enabled', true)) {
            http_response_code(403);
            exit('Registration disabled by admin.');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password_confirmation'] ?? '');

        if ($name === '' || $email === '' || $password === '' || $password !== $password2) {
            flash('error', 'Invalid registration input.');
            redirect_to('/register');
        }

        if ($username === '') {
            $base = preg_replace('/[^a-z0-9_]/', '_', strtolower($name)) ?: 'user';
            $username = $base;
            $i = 1;
            while (true) {
                $stmt = db()->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
                $stmt->execute([$username]);
                if (!$stmt->fetchColumn()) {
                    break;
                }
                $username = $base.'_'.$i;
                $i++;
            }
        }

        $exists = db()->prepare('SELECT 1 FROM users WHERE email = ? OR username = ? LIMIT 1');
        $exists->execute([$email, $username]);
        if ($exists->fetchColumn()) {
            flash('error', 'Email or username already exists.');
            redirect_to('/register');
        }

        $ins = db()->prepare('INSERT INTO users (name, username, email, password, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $emailVerifiedAt = setting_bool('email_verification_enabled', false) ? null : now();
        $ins->execute([$name, $username, $email, password_hash($password, PASSWORD_BCRYPT), $emailVerifiedAt, now(), now()]);
        $userId = (int) db()->lastInsertId();

        $profile = db()->prepare('INSERT INTO profiles (user_id, status_message, created_at, updated_at) VALUES (?, ?, ?, ?)');
        $profile->execute([$userId, 'Hey there! I am using Chatting.', now(), now()]);

        $_SESSION['user_id'] = $userId;
        redirect_to('/chat');
    }

    if ($path === '/' || $path === '/chat') {
        $user = require_auth();

        if ($method === 'GET') {
            render_page('chat_index', [
                'user' => $user,
                'users' => all_users_except((int) $user['id']),
                'conversations' => chat_conversations_for_user((int) $user['id']),
            ]);
            return;
        }

        if ($method === 'POST') {
            verify_csrf_or_fail();
            $target = (int) ($_POST['user_id'] ?? 0);
            if ($target <= 0 || $target === (int) $user['id']) {
                flash('error', 'Invalid user selected.');
                redirect_to('/chat');
            }
            $conversationId = create_or_get_conversation((int) $user['id'], $target);
            redirect_to('/chat/'.$conversationId);
        }
    }

    if (preg_match('#^/chat/(\d+)$#', $path, $m) === 1) {
        $conversationId = (int) $m[1];
        $user = require_auth();
        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        if ($method === 'GET') {
            mark_seen($conversationId, (int) $user['id']);
            render_page('chat_show', [
                'user' => $user,
                'conversationId' => $conversationId,
                'otherUser' => conversation_other_user($conversationId, (int) $user['id']),
                'messages' => conversation_messages($conversationId, 30, 0),
                'settings' => conversation_settings($conversationId, (int) $user['id']),
            ]);
            return;
        }
    }

    if (preg_match('#^/chat/(\d+)/send$#', $path, $m) === 1 && $method === 'POST') {
        verify_csrf_or_fail();
        $conversationId = (int) $m[1];
        $user = require_auth();
        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $body = trim((string) ($_POST['body'] ?? ''));
        if ($body === '') {
            redirect_to('/chat/'.$conversationId);
        }

        $settings = conversation_settings($conversationId, (int) $user['id']);
        $adminHistory = setting_bool('chat_history_enabled', true);
        $expiresAt = (!$adminHistory || (int) $settings['save_history'] !== 1) ? date('Y-m-d H:i:s', time() + 86400) : null;

        $insert = db()->prepare('INSERT INTO messages (conversation_id, sender_id, body, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute([$conversationId, (int) $user['id'], $body, $expiresAt, now(), now()]);
        $messageId = (int) db()->lastInsertId();

        $others = db()->prepare('SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND user_id != ?');
        $others->execute([$conversationId, (int) $user['id']]);
        $rows = $others->fetchAll(PDO::FETCH_COLUMN);

        $reads = db()->prepare('INSERT INTO message_reads (message_id, user_id, delivered_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE delivered_at = VALUES(delivered_at), updated_at = VALUES(updated_at)');
        foreach ($rows as $uid) {
            $reads->execute([$messageId, (int) $uid, now(), now(), now()]);
        }

        redirect_to('/chat/'.$conversationId);
    }

    if (preg_match('#^/chat/(\d+)/settings$#', $path, $m) === 1 && $method === 'POST') {
        verify_csrf_or_fail();
        $conversationId = (int) $m[1];
        $user = require_auth();
        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $encAllowed = setting_bool('e2ee_enabled', false);
        $stmt = db()->prepare('UPDATE user_conversation_settings SET save_history = ?, mute_notifications = ?, encryption_enabled = ?, updated_at = ? WHERE conversation_id = ? AND user_id = ?');
        $stmt->execute([
            !empty($_POST['save_history']) ? 1 : 0,
            !empty($_POST['mute_notifications']) ? 1 : 0,
            ($encAllowed && !empty($_POST['encryption_enabled'])) ? 1 : 0,
            now(),
            $conversationId,
            (int) $user['id'],
        ]);

        redirect_to('/chat/'.$conversationId);
    }

    if (preg_match('#^/chat/(\d+)/poll$#', $path, $m) === 1 && $method === 'GET') {
        $conversationId = (int) $m[1];
        $user = require_auth();
        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            json_response(['error' => 'Forbidden'], 403);
        }

        $afterId = (int) ($_GET['after_id'] ?? 0);
        $stmt = db()->prepare('SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.conversation_id = ? AND m.id > ? AND m.deleted_at IS NULL ORDER BY m.id ASC');
        $stmt->execute([$conversationId, $afterId]);
        $messages = $stmt->fetchAll();

        mark_seen($conversationId, (int) $user['id']);

        json_response([
            'messages' => $messages,
            'typing_users' => typing_users($conversationId, (int) $user['id']),
        ]);
    }

    if (preg_match('#^/chat/(\d+)/typing$#', $path, $m) === 1 && $method === 'POST') {
        $conversationId = (int) $m[1];
        $user = require_auth();
        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            json_response(['error' => 'Forbidden'], 403);
        }
        typing_set($conversationId, (int) $user['id']);
        json_response(['ok' => true]);
    }

    if (preg_match('#^/message/(\d+)/delete-for-everyone$#', $path, $m) === 1 && $method === 'POST') {
        verify_csrf_or_fail();
        $messageId = (int) $m[1];
        $user = require_auth();

        $stmt = db()->prepare('UPDATE messages SET body = ?, is_deleted_for_everyone = 1, updated_at = ? WHERE id = ? AND sender_id = ?');
        $stmt->execute(['[deleted]', now(), $messageId, (int) $user['id']]);

        $back = $_SERVER['HTTP_REFERER'] ?? '/chat';
        redirect_to($back);
    }

    if (preg_match('#^/chat/(\d+)/clear$#', $path, $m) === 1 && $method === 'POST') {
        verify_csrf_or_fail();
        $conversationId = (int) $m[1];
        $user = require_auth();

        if (!user_in_conversation($conversationId, (int) $user['id'])) {
            http_response_code(403);
            exit('Forbidden');
        }

        $stmt = db()->prepare('UPDATE messages SET deleted_at = ?, updated_at = ? WHERE conversation_id = ? AND sender_id = ?');
        $stmt->execute([now(), now(), $conversationId, (int) $user['id']]);

        redirect_to('/chat/'.$conversationId);
    }

    if ($path === '/profile' && $method === 'GET') {
        $user = require_auth();
        render_page('profile', ['user' => $user]);
        return;
    }

    if ($path === '/profile' && $method === 'POST') {
        verify_csrf_or_fail();
        $user = require_auth();

        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $status = trim((string) ($_POST['status_message'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));

        $stmt = db()->prepare('UPDATE users SET name = ?, username = ?, hide_online_status = ?, hide_last_seen = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([
            $name,
            $username,
            !empty($_POST['hide_online_status']) ? 1 : 0,
            !empty($_POST['hide_last_seen']) ? 1 : 0,
            now(),
            (int) $user['id'],
        ]);

        $prof = db()->prepare('INSERT INTO profiles (user_id, status_message, bio, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status_message = VALUES(status_message), bio = VALUES(bio), updated_at = VALUES(updated_at)');
        $prof->execute([(int) $user['id'], $status, $bio, now(), now()]);

        flash('success', 'Profile updated.');
        redirect_to('/profile');
    }

    if ($path === '/admin' && $method === 'GET') {
        $user = require_admin();

        $totUsers = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $totMessages = (int) db()->query('SELECT COUNT(*) FROM messages WHERE deleted_at IS NULL')->fetchColumn();

        $size = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('storage')));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        render_page('admin_dashboard', [
            'user' => $user,
            'totalUsers' => $totUsers,
            'totalMessages' => $totMessages,
            'storageBytes' => $size,
            'settings' => app_settings(),
        ]);
        return;
    }

    if ($path === '/admin/settings' && $method === 'POST') {
        verify_csrf_or_fail();
        $user = require_admin();
        admin_update_settings($_POST['settings'] ?? [], (int) $user['id']);
        flash('success', 'Settings updated.');
        redirect_to('/admin');
    }

    if ($path === '/admin/users' && $method === 'GET') {
        $user = require_admin();
        $users = db()->query('SELECT id, name, username, email, is_admin, is_banned, created_at FROM users ORDER BY id DESC')->fetchAll();
        render_page('admin_users', ['user' => $user, 'users' => $users]);
        return;
    }

    if (preg_match('#^/admin/user/(\d+)/(ban|unban|delete)$#', $path, $m) === 1 && $method === 'POST') {
        verify_csrf_or_fail();
        $admin = require_admin();
        $id = (int) $m[1];
        $action = $m[2];

        if ($id === (int) $admin['id']) {
            flash('error', 'You cannot modify your own admin account here.');
            redirect_to('/admin/users');
        }

        if ($action === 'ban') {
            db()->prepare('UPDATE users SET is_banned = 1, updated_at = ? WHERE id = ? AND is_admin = 0')->execute([now(), $id]);
        } elseif ($action === 'unban') {
            db()->prepare('UPDATE users SET is_banned = 0, updated_at = ? WHERE id = ?')->execute([now(), $id]);
        } else {
            db()->prepare('DELETE FROM users WHERE id = ? AND is_admin = 0')->execute([$id]);
        }

        redirect_to('/admin/users');
    }

    if ($path === '/admin/messages' && $method === 'GET') {
        $user = require_admin();
        if (setting_bool('e2ee_enabled', false)) {
            http_response_code(403);
            exit('Cannot moderate messages while encryption is enabled.');
        }

        $messages = db()->query('SELECT m.id, m.body, m.created_at, u.username FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.deleted_at IS NULL ORDER BY m.id DESC LIMIT 200')->fetchAll();
        render_page('admin_messages', ['user' => $user, 'messages' => $messages]);
        return;
    }

    http_response_code(404);
    render_page('not_found');
}
