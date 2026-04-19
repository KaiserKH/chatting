<div class="bg-white rounded-xl shadow p-5">
    <h1 class="text-xl font-semibold mb-4">Message Moderation</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="py-2">ID</th>
                    <th class="py-2">Sender</th>
                    <th class="py-2">Message</th>
                    <th class="py-2">Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr class="border-b align-top">
                        <td class="py-2"><?= (int) $m['id'] ?></td>
                        <td class="py-2">@<?= e($m['username']) ?></td>
                        <td class="py-2 max-w-2xl break-words"><?= e($m['body']) ?></td>
                        <td class="py-2"><?= e($m['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
