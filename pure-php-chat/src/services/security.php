<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="_csrf" value="'.e(csrf_token()).'">';
}

function verify_csrf_or_fail(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
        http_response_code(419);
        exit('Invalid CSRF token');
    }
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $current = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $current;
}

function redirect_to(string $path): void
{
    header('Location: '.$path);
    exit;
}

function app_settings(): array
{
    static $settings = null;
    if (is_array($settings)) {
        return $settings;
    }

    $settings = [
        'e2ee_enabled' => false,
        'chat_history_enabled' => true,
        'media_upload_enabled' => false,
        'friend_only_messaging_enabled' => false,
        'registration_enabled' => true,
        'email_verification_enabled' => false,
    ];

    $rows = db()->query('SELECT setting_key, setting_value FROM admin_settings')->fetchAll();
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = filter_var($row['setting_value'], FILTER_VALIDATE_BOOLEAN);
    }

    return $settings;
}

function setting_bool(string $key, bool $default = false): bool
{
    $settings = app_settings();
    return array_key_exists($key, $settings) ? (bool) $settings[$key] : $default;
}

function invalidate_settings_cache(): void
{
    $GLOBALS['__reset_settings_cache__'] = true;
}
