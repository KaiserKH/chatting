<?php

declare(strict_types=1);

function all_users_except(int $id): array
{
    $stmt = db()->prepare('SELECT id, name, username, is_banned FROM users WHERE id != ? ORDER BY name ASC');
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

function chat_conversations_for_user(int $userId): array
{
    $sql = 'SELECT c.id,
                u.id AS other_user_id,
                u.name AS other_name,
                u.username AS other_username,
                p.status_message,
                (SELECT COUNT(*) FROM messages m
                  LEFT JOIN message_reads mr ON mr.message_id = m.id AND mr.user_id = ?
                  WHERE m.conversation_id = c.id
                    AND m.sender_id != ?
                    AND (mr.seen_at IS NULL)) AS unread_count,
                (SELECT MAX(m2.id) FROM messages m2 WHERE m2.conversation_id = c.id) AS latest_message_id
            FROM conversations c
            JOIN conversation_participants cp1 ON cp1.conversation_id = c.id AND cp1.user_id = ?
            JOIN conversation_participants cp2 ON cp2.conversation_id = c.id AND cp2.user_id != ?
            JOIN users u ON u.id = cp2.user_id
            LEFT JOIN profiles p ON p.user_id = u.id
            WHERE c.deleted_at IS NULL
            ORDER BY latest_message_id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute([$userId, $userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function create_or_get_conversation(int $currentUserId, int $otherUserId): int
{
    $sql = 'SELECT c.id
            FROM conversations c
            JOIN conversation_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ?
            JOIN conversation_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ?
            WHERE c.deleted_at IS NULL
            LIMIT 1';
    $stmt = db()->prepare($sql);
    $stmt->execute([$currentUserId, $otherUserId]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        return (int) $existing;
    }

    db()->beginTransaction();
    $insConv = db()->prepare('INSERT INTO conversations (created_by, created_at, updated_at) VALUES (?, ?, ?)');
    $insConv->execute([$currentUserId, now(), now()]);
    $conversationId = (int) db()->lastInsertId();

    $insPart = db()->prepare('INSERT INTO conversation_participants (conversation_id, user_id, joined_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach ([$currentUserId, $otherUserId] as $uid) {
        $insPart->execute([$conversationId, $uid, now(), now(), now()]);
    }

    $insSetting = db()->prepare('INSERT INTO user_conversation_settings (conversation_id, user_id, save_history, mute_notifications, encryption_enabled, created_at, updated_at) VALUES (?, ?, 1, 0, 0, ?, ?)');
    foreach ([$currentUserId, $otherUserId] as $uid) {
        $insSetting->execute([$conversationId, $uid, now(), now()]);
    }

    db()->commit();
    return $conversationId;
}

function user_in_conversation(int $conversationId, int $userId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM conversation_participants WHERE conversation_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$conversationId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function conversation_other_user(int $conversationId, int $userId): ?array
{
    $stmt = db()->prepare('SELECT u.*, p.status_message, p.bio, p.avatar_path FROM conversation_participants cp JOIN users u ON u.id = cp.user_id LEFT JOIN profiles p ON p.user_id = u.id WHERE cp.conversation_id = ? AND cp.user_id != ? LIMIT 1');
    $stmt->execute([$conversationId, $userId]);
    return $stmt->fetch() ?: null;
}

function conversation_messages(int $conversationId, int $limit = 25, int $offset = 0): array
{
    $stmt = db()->prepare('SELECT m.*, u.name AS sender_name, u.username AS sender_username FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.conversation_id = ? AND m.deleted_at IS NULL ORDER BY m.id DESC LIMIT ? OFFSET ?');
    $stmt->bindValue(1, $conversationId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_reverse($stmt->fetchAll());
}

function conversation_settings(int $conversationId, int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM user_conversation_settings WHERE conversation_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$conversationId, $userId]);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    $insert = db()->prepare('INSERT INTO user_conversation_settings (conversation_id, user_id, save_history, mute_notifications, encryption_enabled, created_at, updated_at) VALUES (?, ?, 1, 0, 0, ?, ?)');
    $insert->execute([$conversationId, $userId, now(), now()]);

    return [
        'conversation_id' => $conversationId,
        'user_id' => $userId,
        'save_history' => 1,
        'mute_notifications' => 0,
        'encryption_enabled' => 0,
    ];
}

function mark_seen(int $conversationId, int $userId): void
{
    $stmt = db()->prepare('SELECT id FROM messages WHERE conversation_id = ? AND sender_id != ?');
    $stmt->execute([$conversationId, $userId]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $upsert = db()->prepare('INSERT INTO message_reads (message_id, user_id, delivered_at, seen_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE delivered_at = VALUES(delivered_at), seen_at = VALUES(seen_at), updated_at = VALUES(updated_at)');

    foreach ($ids as $id) {
        $upsert->execute([(int) $id, $userId, now(), now(), now(), now()]);
    }

    $latest = db()->prepare('SELECT MAX(id) FROM messages WHERE conversation_id = ?');
    $latest->execute([$conversationId]);
    $latestId = (int) ($latest->fetchColumn() ?: 0);
    if ($latestId > 0) {
        $pivot = db()->prepare('UPDATE conversation_participants SET last_read_message_id = ?, updated_at = ? WHERE conversation_id = ? AND user_id = ?');
        $pivot->execute([$latestId, now(), $conversationId, $userId]);
    }
}

function typing_set(int $conversationId, int $userId): void
{
    $file = base_path('storage/typing/'.$conversationId.'_'.$userId.'.json');
    file_put_contents($file, json_encode(['time' => time()], JSON_THROW_ON_ERROR));
}

function typing_users(int $conversationId, int $excludeUserId): array
{
    $users = [];
    $files = glob(base_path('storage/typing/'.$conversationId.'_*.json')) ?: [];
    foreach ($files as $file) {
        if (filemtime($file) === false || filemtime($file) < (time() - 10)) {
            @unlink($file);
            continue;
        }

        if (preg_match('/_(\d+)\.json$/', $file, $m) !== 1) {
            continue;
        }

        $uid = (int) $m[1];
        if ($uid === $excludeUserId) {
            continue;
        }

        $stmt = db()->prepare('SELECT id, name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$uid]);
        $user = $stmt->fetch();
        if ($user) {
            $users[] = ['id' => (int) $user['id'], 'name' => $user['name']];
        }
    }

    return $users;
}
