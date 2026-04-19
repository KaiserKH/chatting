<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chats
            </h2>
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="text-sm text-pink-700 hover:text-pink-900">Admin Panel</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-3">
            <div class="bg-white shadow-sm sm:rounded-lg p-5 lg:col-span-1">
                <h3 class="font-semibold text-lg text-gray-900">Start a Conversation</h3>
                <form method="POST" action="{{ route('chat.start') }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500" required>
                        <option value="">Select user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ '@'.$user->username }})</option>
                        @endforeach
                    </select>
                    <x-primary-button>Create / Open Chat</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-5 lg:col-span-2">
                <h3 class="font-semibold text-lg text-gray-900">Your Conversations</h3>
                <div class="mt-4 divide-y divide-gray-100">
                    @forelse($conversations as $conversation)
                        @php
                            $other = $conversation->participants->firstWhere('id', '!=', auth()->id());
                        @endphp
                        <a href="{{ route('chat.show', $conversation) }}" class="py-3 flex items-center justify-between hover:bg-pink-50 px-2 rounded-lg transition">
                            <div>
                                <p class="font-medium text-gray-900">{{ $other?->name ?? 'Conversation #'.$conversation->id }}</p>
                                <p class="text-sm text-gray-500">{{ $other?->profile?->status_message ?? 'No status message' }}</p>
                            </div>
                            @if($conversation->unread_count > 0)
                                <span class="text-xs bg-pink-600 text-white rounded-full px-2 py-1">{{ $conversation->unread_count }}</span>
                            @endif
                        </a>
                    @empty
                        <p class="text-gray-600 py-6">No conversations yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
