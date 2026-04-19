<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Message Moderation</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-5 rounded-lg shadow-sm">
                <form method="GET" class="mb-4">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search message text" class="rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 w-full md:w-96" />
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="py-2">ID</th>
                                <th class="py-2">Sender</th>
                                <th class="py-2">Conversation</th>
                                <th class="py-2">Message</th>
                                <th class="py-2">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                                <tr class="border-b align-top">
                                    <td class="py-2">{{ $message->id }}</td>
                                    <td class="py-2">{{ $message->sender?->username }}</td>
                                    <td class="py-2">#{{ $message->conversation_id }}</td>
                                    <td class="py-2 max-w-xl break-words">{{ $message->body }}</td>
                                    <td class="py-2">{{ $message->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $messages->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
