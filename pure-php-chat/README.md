# Chatting Pure PHP (No Framework)

This folder is a full PHP conversion of the chat app with the same core behavior and architecture goals:
- Private 1-to-1 chat
- Admin feature toggles
- Polling realtime (3s)
- Typing indicator
- Read receipts basics
- Profile management
- Ban/unban/delete users
- Storage-aware message expiration
- Shared-hosting friendly deployment

## Folder structure

```text
pure-php-chat/
├── public/
│   ├── .htaccess
│   └── index.php
├── src/
│   ├── bootstrap.php
│   ├── pages/
│   ├── partials/
│   └── services/
├── database/
│   └── schema.sql
├── scripts/
│   └── cleanup.php
├── storage/
│   ├── sessions/
│   └── typing/
├── config.example.php
└── .gitignore
```

## Localhost testing

1. Requirements:
- PHP 8.2+
- MySQL 8+

2. Create config:
- From pure-php-chat directory:
```bash
cp config/config.example.php config/config.php
```

3. Create database and import schema:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS chatting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p chatting < database/schema.sql
```

4. Update DB credentials in config/config.php.

5. Start local server:
```bash
php -S 127.0.0.1:8080 -t public
```

6. Open browser:
- http://127.0.0.1:8080

Default users:
- admin@example.com / password
- test@example.com / password

## Cron / cleanup

Run every day (shared hosting cron):
```bash
php /home/USER/public_html/pure-php-chat/scripts/cleanup.php
```

This removes expired messages and old typing marker files.

## Shared hosting deployment (cPanel)

1. Upload pure-php-chat folder.
2. Point domain/subdomain document root to pure-php-chat/public.
3. Create MySQL DB and import database/schema.sql from phpMyAdmin or terminal.
4. Create config/config.php from config.example.php and set DB credentials.
5. Ensure storage/sessions and storage/typing are writable.
6. Add daily cron for scripts/cleanup.php.

## Security notes

- CSRF token verification for all forms.
- Password hashing with password_hash(PASSWORD_BCRYPT).
- Prepared statements (PDO) to prevent SQL injection.
- Basic login rate limiting per IP in session.
- Remember-me secure cookie.

## What is phase-ready vs placeholder

Implemented now:
- Authentication, profile, chat, admin toggles, moderation, cleanup.

Placeholders for future expansion:
- Full E2EE key exchange/runtime encryption
- Media upload pipeline
- Friend-only enforcement

These are toggle-gated in admin settings and can be extended in this pure PHP stack.
