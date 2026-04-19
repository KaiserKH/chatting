<?php

declare(strict_types=1);

function current_user(): ?array
{
    static $user = null;
    if (is_array($user)) {
        return $user;
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId && !empty($_COOKIE['remember_token'])) {
        $stmt = db()->prepare('SELECT * FROM users WHERE remember_token = ? LIMIT 1');
        $stmt->execute([$_COOKIE['remember_token']]);
        $remembered = $stmt->fetch();
        if ($remembered) {
            $_SESSION['user_id'] = (int) $remembered['id'];
            $userId = (int) $remembered['id'];
        }
    }

    if (!$userId) {
        return null;
    }

    $stmt = db()->prepare('SELECT u.*, p.status_message, p.bio, p.avatar_path FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.id = ? LIMIT 1');
    $stmt->execute([(int) $userId]);
    $user = $stmt->fetch() ?: null;

    return $user;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('/login');
    }

    if ((int) $user['is_banned'] === 1) {
        logout_user();
        flash('error', 'Your account is banned.');
        redirect_to('/login');
    }

    if (setting_bool('email_verification_enabled', false) && empty($user['email_verified_at'])) {
        http_response_code(403);
        exit('Email verification is required by admin setting.');
    }

    return $user;
}

function require_admin(): array
{
    $user = require_auth();
    if ((int) $user['is_admin'] !== 1) {
        http_response_code(403);
        exit('Admin access required.');
    }

    return $user;
}

function refresh_last_seen(): void
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        return;
    }

    $stmt = db()->prepare('UPDATE users SET last_seen_at = ? WHERE id = ?');
    $stmt->execute([now(), (int) $userId]);
}

function authenticate(string $login, string $password, bool $remember): bool
{
    $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    $stmt = db()->prepare("SELECT * FROM users WHERE {$field} = ? LIMIT 1");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    if ((int) $user['is_banned'] === 1) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $days = (int) ((require base_path('config/config.php'))['remember_days'] ?? 30);
        setcookie('remember_token', $token, [
            'expires' => time() + ($days * 86400),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        $update = db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
        $update->execute([$token, (int) $user['id']]);
    }

    return true;
}

function logout_user(): void
{
    if (!empty($_SESSION['user_id'])) {
        $stmt = db()->prepare('UPDATE users SET remember_token = NULL, last_seen_at = ? WHERE id = ?');
        $stmt->execute([now(), (int) $_SESSION['user_id']]);
    }

    $_SESSION = [];
    setcookie(session_name(), '', time() - 3600, '/');
    setcookie('remember_token', '', time() - 3600, '/');
    session_destroy();
}
