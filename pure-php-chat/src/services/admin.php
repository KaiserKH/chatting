<?php

declare(strict_types=1);

function admin_update_settings(array $settings, int $adminId): void
{
    $allowed = [
        'e2ee_enabled',
        'chat_history_enabled',
        'media_upload_enabled',
        'friend_only_messaging_enabled',
        'registration_enabled',
        'email_verification_enabled',
    ];

    $stmt = db()->prepare('INSERT INTO admin_settings (setting_key, setting_value, updated_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)');

    foreach ($allowed as $key) {
        $value = !empty($settings[$key]) ? '1' : '0';
        $stmt->execute([$key, $value, $adminId, now(), now()]);
    }
}
