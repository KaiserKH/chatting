<div class="max-w-2xl bg-white rounded-xl shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Profile</h1>
    <form method="POST" action="/profile" class="space-y-4">
        <?= csrf_input() ?>
        <div>
            <label class="block text-sm mb-1">Name</label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Username</label>
            <input type="text" name="username" value="<?= e($user['username']) ?>" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Status message</label>
            <input type="text" name="status_message" value="<?= e($user['status_message'] ?? '') ?>" class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Bio</label>
            <textarea name="bio" rows="4" class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500"><?= e($user['bio'] ?? '') ?></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="hide_online_status" value="1" class="rounded" <?= ((int) $user['hide_online_status'] === 1) ? 'checked' : '' ?>>
            Hide online status
        </label>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="hide_last_seen" value="1" class="rounded" <?= ((int) $user['hide_last_seen'] === 1) ? 'checked' : '' ?>>
            Hide last seen
        </label>
        <button class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg px-4 py-2">Save profile</button>
    </form>
</div>
