<div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Register</h1>
    <form method="POST" action="/register" class="space-y-3">
        <?= csrf_input() ?>
        <div>
            <label class="block text-sm mb-1">Name</label>
            <input type="text" name="name" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Username (optional)</label>
            <input type="text" name="username" class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="email" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Password</label>
            <input type="password" name="password" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <button class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-lg py-2">Create account</button>
    </form>
</div>
