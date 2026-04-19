<div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
    <h1 class="text-xl font-semibold mb-4">Login</h1>
    <form method="POST" action="/login" class="space-y-3">
        <?= csrf_input() ?>
        <div>
            <label class="block text-sm mb-1">Username or Email</label>
            <input type="text" name="login" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <div>
            <label class="block text-sm mb-1">Password</label>
            <input type="password" name="password" required class="w-full rounded-lg border-zinc-300 focus:border-rose-500 focus:ring-rose-500">
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-rose-600">
            Remember me
        </label>
        <button class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-lg py-2">Login</button>
    </form>
</div>
