# Chatting (Laravel 11)

Private one-to-one chat app for friends/family, designed for shared hosting, low storage use, and admin-controlled privacy features.

## Current status

Phase 1 is fully implemented in this repository:
- Authentication with email + unique username.
- Profile management (status, bio, privacy flags, last seen).
- Private one-to-one chat.
- Polling-based realtime updates every 3 seconds.
- Typing indicator.
- Read tracking.
- Admin dashboard with global feature toggles.
- Admin user moderation (ban/unban/delete).
- Admin message moderation blocked when encryption toggle is ON.
- Storage optimization starter: expiring messages + cleanup command + daily scheduler.

Phase 2 and Phase 3 implementation plans are included below with exact module breakdown.

## Stack

- Laravel 11
- PHP 8.4
- Blade + Alpine.js + Tailwind CSS
- Queue driver: database
- Session driver: database
- Database target: MySQL (dev can use SQLite)

## Project structure

The important structure (trimmed) is:

```text
chatting/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Auth/
│   │   │   ├── ChatController.php
│   │   │   ├── MessageController.php
│   │   │   └── ...
│   │   ├── Middleware/
│   │   └── Requests/
│   └── Models/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── database.sql
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── admin/
│       ├── auth/
│       ├── chat/
│       ├── layouts/
│       └── profile/
├── routes/
├── storage/
├── tests/
├── .htaccess.shared-hosting
├── artisan
├── composer.json
└── package.json
```

## Phase 1 modules (implemented)

### Migrations
- users extension: username, admin/ban flags, last seen, privacy flags
- profiles
- conversations
- conversation_participants
- messages
- message_reads
- admin_settings
- user_conversation_settings

### Models
- User
- Profile
- Conversation
- Message
- MessageRead
- AdminSetting
- UserConversationSetting

### Controllers
- ChatController
- MessageController
- ConversationSettingController
- TypingController
- ProfileController (extended)
- AuthenticatedSessionController (extended)
- RegisteredUserController (extended)
- Admin\DashboardController
- Admin\SettingController
- Admin\UserManagementController
- Admin\MessageModerationController

### Middleware
- EnsureAdmin
- EnsureNotBanned
- EnsureRegistrationEnabled
- EnsureEmailVerificationIfEnabled

### Routes
- chat routes under authenticated users
- admin routes under /admin
- root route returns welcome for guests, chat redirect for logged-in users

### Blade UI
- Chat list page
- Chat room page with polling and typing indicator
- Admin dashboard
- Admin users page
- Admin messages moderation page
- Theme switcher: light / dark / romantic

## Phase 1 setup

1. Install dependencies:
	- composer install
	- npm install
2. Configure environment:
	- cp .env.example .env
	- php artisan key:generate
	- Set MySQL values in .env:
	  - DB_CONNECTION=mysql
	  - DB_HOST
	  - DB_PORT
	  - DB_DATABASE
	  - DB_USERNAME
	  - DB_PASSWORD
3. Run database setup:
	- php artisan migrate --seed
4. Build frontend:
	- npm run build
5. Run locally:
	- php artisan serve
6. Default seeded accounts:
	- admin@example.com / password
	- test@example.com / password

## Localhost testing guide

Use this flow for a full local test:

1. Backend and frontend install:
	- composer install
	- npm install
2. Environment:
	- cp .env.example .env
	- php artisan key:generate
	- Update .env for MySQL (recommended) or SQLite.
3. MySQL option:
	- Create database and import schema from database/database.sql (see next section), or run migrations.
4. Run app setup:
	- php artisan migrate --seed
	- php artisan storage:link
5. Build assets:
	- npm run build
6. Run app:
	- php artisan serve
7. Optional realtime frontend watch during development:
	- npm run dev
8. Run tests:
	- php artisan test

## MySQL database creation (SQL file)

This repository now includes:
- database/database.sql

You can create and import with:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS chatting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p chatting < database/database.sql
```

Then set .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatting
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Note: If you import database/database.sql, do not run migrate:fresh. Use migrate only for future schema updates.

## Admin feature toggles (Phase 1)

Admin page supports:
- End-to-End Encryption toggle (used as global switch gate)
- Chat History Saving toggle
- Media Uploads toggle (Phase 2 wiring)
- Friend-only Messaging toggle (Phase 2 wiring)
- User Registration toggle
- Email Verification toggle

Rules already enforced:
- Registration can be blocked globally.
- Email verification can be enforced globally.
- Chat history can be globally disabled.
- Per-user conversation save setting only applies when global history is enabled.
- Message moderation page is blocked while encryption toggle is ON.

## Storage optimization (Phase 1)

- If chat history is disabled globally OR user turns off Save history in a conversation, new messages in that conversation get expires_at = now + 24h.
- Cleanup command:
  - php artisan chat:cleanup-expired-messages
- Scheduler entries:
  - chat:cleanup-expired-messages daily at 02:10
  - chat:cleanup-unused-media daily at 02:30 (placeholder for Phase 2)

## Phase 2 plan (friend system + media)

### Migrations to add
- friendships
- media_files
- message_media (optional pivot if one message can contain many files)
- reports (recommended for moderation)

### Models to add
- Friendship
- MediaFile
- Report

### Controllers to add
- FriendshipController
- MediaController
- ReportController

### Routes to add
- /friends (send/accept/reject/block)
- /media/upload
- /media/{id}/signed-url
- /report

### Blade to add
- friends index/requests
- upload preview component
- report modal partial

### Setup steps
1. Add intervention/image or imagick-based compression path.
2. Validate image MIME + 2MB max.
3. Save files in storage/app/private/media.
4. Serve with signed URLs only.
5. Add orphan media cleanup job + cron.

## Phase 3 plan (E2EE + advanced privacy)

### Migrations to add
- user_public_keys
- message_cipher_meta (nonce, algorithm, key version)

### Models to add
- UserPublicKey
- MessageCipherMeta

### Controllers/services to add
- EncryptionKeyController
- EncryptedMessageController
- E2EE service abstraction

### Routes to add
- /keys/public
- /keys/rotate
- encrypted send/poll endpoints (or upgrade current endpoints)

### Blade/JS to add
- key generation prompt
- key backup reminder
- browser-side encryption/decryption flow with TweetNaCl

### Setup steps
1. Keep private key only in browser localStorage (or IndexedDB).
2. Store only public key server-side.
3. Encrypt in browser before POST.
4. Store ciphertext + nonce only.
5. Disable admin moderation reads when E2EE ON.

## Shared hosting deployment (cPanel)

### Standard deployment
1. Upload project files.
2. Point domain document root to public.
3. Set .env for production and MySQL.
4. Run:
	- php artisan migrate --force
	- php artisan db:seed --force
	- php artisan storage:link
	- php artisan config:cache
	- php artisan route:cache
	- php artisan view:cache
5. Build assets locally and upload public/build if node is not available on host.

### If you cannot point document root to public
Use the root-level rewrite file in this repo:
- .htaccess.shared-hosting

Then copy its content into your hosting root .htaccess.

### Cron jobs (daily)
- * * * * * php /home/USER/app/artisan schedule:run >> /dev/null 2>&1

This single cron executes the Laravel scheduler (which runs cleanup jobs daily).

## GitHub push workflow

After verifying locally, push to your repository:

```bash
git add .
git commit -m "Build Phase 1 private chat app with admin controls"
git push origin main
```

If this is a new remote:

```bash
git remote add origin https://github.com/<YOUR_USERNAME>/<YOUR_REPO>.git
git branch -M main
git push -u origin main
```
