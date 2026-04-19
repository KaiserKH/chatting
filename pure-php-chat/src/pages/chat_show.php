<div class="grid gap-6 lg:grid-cols-3" x-data="chatRoom()" x-init="init()">
    <section class="bg-white rounded-xl shadow p-5">
        <h2 class="text-lg font-semibold"><?= e($otherUser['name'] ?? 'Chat') ?> (@<?= e($otherUser['username'] ?? 'user') ?>)</h2>
        <p class="text-sm text-zinc-500 mt-1"><?= e($otherUser['status_message'] ?? 'No status') ?></p>

        <form method="POST" action="/chat/<?= (int) $conversationId ?>/settings" class="mt-4 space-y-2">
            <?= csrf_input() ?>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="save_history" value="1" class="rounded" <?= ((int) $settings['save_history'] === 1) ? 'checked' : '' ?>>
                Save history
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="mute_notifications" value="1" class="rounded" <?= ((int) $settings['mute_notifications'] === 1) ? 'checked' : '' ?>>
                Mute notifications
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="encryption_enabled" value="1" class="rounded" <?= ((int) $settings['encryption_enabled'] === 1) ? 'checked' : '' ?>>
                Encryption (if admin allows)
            </label>
            <button class="w-full bg-zinc-900 text-white rounded-lg py-2">Save Settings</button>
        </form>

        <form method="POST" action="/chat/<?= (int) $conversationId ?>/clear" class="mt-4">
            <?= csrf_input() ?>
            <button class="text-red-600 text-sm">Clear my messages</button>
        </form>
    </section>

    <section class="bg-white rounded-xl shadow p-5 lg:col-span-2">
        <div id="message-list" class="h-[60vh] overflow-y-auto space-y-3 pr-2">
            <?php $lastId = 0; foreach ($messages as $m): $lastId = max($lastId, (int) $m['id']); $mine = ((int) $m['sender_id'] === (int) $user['id']); ?>
                <div data-id="<?= (int) $m['id'] ?>" class="<?= $mine ? 'text-right' : 'text-left' ?>">
                    <div class="inline-block max-w-[80%] rounded-2xl px-4 py-2 <?= $mine ? 'bg-rose-600 text-white' : 'bg-zinc-100' ?>">
                        <p class="whitespace-pre-wrap break-words"><?= e($m['body']) ?></p>
                        <p class="text-[11px] opacity-70 mt-1"><?= e(date('H:i', strtotime($m['created_at']))) ?></p>
                    </div>
                    <?php if ($mine): ?>
                        <form method="POST" action="/message/<?= (int) $m['id'] ?>/delete-for-everyone" class="mt-1">
                            <?= csrf_input() ?>
                            <button class="text-[11px] text-red-600">Delete for everyone</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <p x-show="typingUsers.length" class="text-sm text-zinc-500 mt-2" x-text="typingUsers.map(u => u.name).join(', ') + ' typing...'" x-cloak></p>

        <form method="POST" action="/chat/<?= (int) $conversationId ?>/send" class="mt-4 space-y-2">
            <?= csrf_input() ?>
            <textarea name="body" rows="3" required maxlength="4000" @input.debounce.400ms="notifyTyping" class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500" placeholder="Type message..."></textarea>
            <div class="flex justify-between items-center">
                <a href="/chat" class="text-sm text-zinc-500 hover:text-zinc-800">Back</a>
                <button class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg px-4 py-2">Send</button>
            </div>
        </form>
    </section>
</div>

<script>
function chatRoom() {
    return {
        lastId: <?= (int) $lastId ?>,
        typingUsers: [],
        async init() {
            const list = document.getElementById('message-list');
            if (list) list.scrollTop = list.scrollHeight;
            setInterval(() => this.poll(), 3000);
        },
        async poll() {
            const res = await fetch('/chat/<?= (int) $conversationId ?>/poll?after_id=' + this.lastId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            this.typingUsers = data.typing_users || [];
            for (const m of data.messages || []) {
                this.append(m);
            }
        },
        append(message) {
            const list = document.getElementById('message-list');
            if (!list || list.querySelector('[data-id="' + message.id + '"]')) return;
            const mine = Number(message.sender_id) === Number(<?= (int) $user['id'] ?>);
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-id', String(message.id));
            wrapper.className = mine ? 'text-right' : 'text-left';
            wrapper.innerHTML = `<div class="inline-block max-w-[80%] rounded-2xl px-4 py-2 ${mine ? 'bg-rose-600 text-white' : 'bg-zinc-100'}"><p class="whitespace-pre-wrap break-words"></p><p class="text-[11px] opacity-70 mt-1"></p></div>`;
            wrapper.querySelector('p').textContent = message.body;
            wrapper.querySelectorAll('p')[1].textContent = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
            list.appendChild(wrapper);
            this.lastId = Math.max(this.lastId, Number(message.id));
            list.scrollTop = list.scrollHeight;
        },
        async notifyTyping() {
            await fetch('/chat/<?= (int) $conversationId ?>/typing', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'typing=1' });
        }
    }
}
</script>
