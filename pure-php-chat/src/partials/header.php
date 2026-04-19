<?php $authUser = current_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e((require base_path('config/config.php'))['app_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-rose-50 via-orange-50 to-amber-100 text-zinc-900">
    <nav class="bg-white/90 backdrop-blur border-b border-zinc-200">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/chat" class="font-semibold text-lg text-rose-700">Chatting Pure PHP</a>
            <div class="flex items-center gap-4 text-sm">
                <?php if ($authUser): ?>
                    <a href="/chat" class="hover:text-rose-700">Chats</a>
                    <a href="/profile" class="hover:text-rose-700">Profile</a>
                    <?php if ((int) $authUser['is_admin'] === 1): ?>
                        <a href="/admin" class="hover:text-rose-700">Admin</a>
                    <?php endif; ?>
                    <form method="POST" action="/logout" class="inline">
                        <?= csrf_input() ?>
                        <button class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/login" class="hover:text-rose-700">Login</a>
                    <a href="/register" class="hover:text-rose-700">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <?php if ($msg = flash('success')): ?>
            <div class="mb-4 rounded-lg bg-emerald-100 text-emerald-800 px-4 py-2"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-2"><?= e($msg) ?></div>
        <?php endif; ?>
