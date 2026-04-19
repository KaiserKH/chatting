<div class="bg-white rounded-xl shadow p-5">
    <h1 class="text-xl font-semibold mb-4">User Management</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="py-2">User</th>
                    <th class="py-2">Email</th>
                    <th class="py-2">Role</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr class="border-b">
                        <td class="py-2"><?= e($u['name']) ?> (@<?= e($u['username']) ?>)</td>
                        <td class="py-2"><?= e($u['email']) ?></td>
                        <td class="py-2"><?= ((int) $u['is_admin'] === 1) ? 'Admin' : 'User' ?></td>
                        <td class="py-2"><?= ((int) $u['is_banned'] === 1) ? 'Banned' : 'Active' ?></td>
                        <td class="py-2">
                            <?php if ((int) $u['is_admin'] !== 1): ?>
                                <div class="flex gap-2">
                                    <?php if ((int) $u['is_banned'] === 1): ?>
                                        <form method="POST" action="/admin/user/<?= (int) $u['id'] ?>/unban"><?= csrf_input() ?><button class="text-green-600">Unban</button></form>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/user/<?= (int) $u['id'] ?>/ban"><?= csrf_input() ?><button class="text-amber-600">Ban</button></form>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/user/<?= (int) $u['id'] ?>/delete" onsubmit="return confirm('Delete this user?')"><?= csrf_input() ?><button class="text-red-600">Delete</button></form>
                                </div>
                            <?php else: ?>
                                <span class="text-zinc-500">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
