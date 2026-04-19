<div class="grid gap-6 lg:grid-cols-3">
    <section class="bg-white rounded-xl shadow p-5">
        <h2 class="text-lg font-semibold">Start Conversation</h2>
        <form method="POST" action="/chat" class="mt-4 space-y-3">
            <?= csrf_input() ?>
            <select name="user_id" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
                <option value="">Select user</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= ((int) $u['is_banned'] === 1) ? 'disabled' : '' ?>>
                        <?= e($u['name']) ?> (@<?= e($u['username']) ?>)<?= ((int) $u['is_banned'] === 1) ? ' - banned' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-lg py-2">Open Chat</button>
        </form>
    </section>

    <section class="bg-white rounded-xl shadow p-5 lg:col-span-2">
        <h2 class="text-lg font-semibold">Your Chats</h2>
        <div class="mt-4 divide-y">
            <?php if (!$conversations): ?>
                <p class="py-4 text-zinc-500">No conversations yet.</p>
            <?php endif; ?>
            <?php foreach ($conversations as $conv): ?>
                <a href="/chat/<?= (int) $conv['id'] ?>" class="flex justify-between items-center py-3 hover:bg-rose-50 px-2 rounded-lg">
                    <div>
                        <p class="font-medium"><?= e($conv['other_name']) ?> (@<?= e($conv['other_username']) ?>)</p>
                        <p class="text-sm text-zinc-500"><?= e($conv['status_message'] ?: 'No status') ?></p>
                    </div>
                    <?php if ((int) $conv['unread_count'] > 0): ?>
                        <span class="bg-rose-600 text-white text-xs px-2 py-1 rounded-full"><?= (int) $conv['unread_count'] ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
