<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-5 rounded-lg shadow-sm">
                <form method="GET" class="mb-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users" class="rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 w-full md:w-96" />
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2">User</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-b">
                                    <td class="py-2">{{ $user->name }} ({{ '@'.$user->username }})</td>
                                    <td class="py-2">{{ $user->email }}</td>
                                    <td class="py-2">{{ $user->is_banned ? 'Banned' : 'Active' }}</td>
                                    <td class="py-2 flex gap-2">
                                        @if(!$user->is_admin)
                                            @if($user->is_banned)
                                                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-green-600 hover:text-green-800">Unban</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="text-amber-600 hover:text-amber-800">Ban</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        @else
                                            <span class="text-gray-500">Admin</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
