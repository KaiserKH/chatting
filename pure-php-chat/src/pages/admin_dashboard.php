<div class="space-y-6">
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-zinc-500">Total users</p>
            <p class="text-2xl font-semibold"><?= (int) $totalUsers ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-zinc-500">Total messages</p>
            <p class="text-2xl font-semibold"><?= (int) $totalMessages ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-zinc-500">Storage used</p>
            <p class="text-2xl font-semibold"><?= number_format($storageBytes / 1024 / 1024, 2) ?> MB</p>
            <p class="text-xs text-zinc-400">Limit target: 5GB</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-lg font-semibold mb-3">Global feature toggles</h2>
        <form method="POST" action="/admin/settings" class="grid md:grid-cols-2 gap-2">
            <?= csrf_input() ?>
            <?php
            $labels = [
                'e2ee_enabled' => 'Enable End-to-End Encryption',
                'chat_history_enabled' => 'Enable Chat History Saving',
                'media_upload_enabled' => 'Enable Media Uploads',
                'friend_only_messaging_enabled' => 'Enable Friend-only Messaging',
                'registration_enabled' => 'Enable User Registration',
                'email_verification_enabled' => 'Enable Email Verification',
            ];
            foreach ($labels as $k => $label):
            ?>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="settings[<?= e($k) ?>]" value="1" class="rounded" <?= !empty($settings[$k]) ? 'checked' : '' ?>>
                    <?= e($label) ?>
                </label>
            <?php endforeach; ?>
            <div class="md:col-span-2 mt-2">
                <button class="bg-zinc-900 text-white rounded-lg px-4 py-2">Save</button>
            </div>
        </form>
    </div>

    <div class="text-sm flex gap-4">
        <a href="/admin/users" class="text-rose-700 hover:text-rose-900">Manage users</a>
        <a href="/admin/messages" class="text-rose-700 hover:text-rose-900">Moderate messages</a>
    </div>
</div>
