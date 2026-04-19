<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                @php
                    $other = $conversation->participants->firstWhere('id', '!=', auth()->id());
                @endphp
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $other?->name ?? 'Chat' }}</h2>
                <p class="text-sm text-gray-500">{{ $other?->profile?->status_message ?? 'No status message' }}</p>
            </div>
            <a href="{{ route('chat.index') }}" class="text-sm text-pink-700 hover:text-pink-900">Back to chats</a>
        </div>
    </x-slot>

    <div
        class="py-6"
        x-data="chatRoom({
            pollUrl: '{{ route('chat.poll', $conversation) }}',
            typingUrl: '{{ route('chat.typing', $conversation) }}',
            csrfToken: '{{ csrf_token() }}',
            initialLastId: {{ (int) ($messages->last()?->id ?? 0) }}
        })"
        x-init="init()"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-3">
            <div class="bg-white shadow-sm sm:rounded-lg p-5 lg:col-span-1 space-y-4">
                <h3 class="font-semibold text-lg text-gray-900">Conversation Settings</h3>
                <form method="POST" action="{{ route('chat.settings.update', $conversation) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="save_history" value="1" @checked($settings->save_history) class="rounded border-gray-300 text-pink-600">
                        Save chat history
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="mute_notifications" value="1" @checked($settings->mute_notifications) class="rounded border-gray-300 text-pink-600">
                        Mute notifications
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="encryption_enabled" value="1" @checked($settings->encryption_enabled) class="rounded border-gray-300 text-pink-600">
                        Encryption (if admin enabled)
                    </label>
                    <x-primary-button>Save Settings</x-primary-button>
                </form>

                <form method="POST" action="{{ route('chat.clear', $conversation) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Clear my messages</button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-5 lg:col-span-2">
                <div id="message-list" class="h-[60vh] overflow-y-auto space-y-3 pr-2">
                    @foreach($messages as $message)
                        <div class="{{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}" data-message-id="{{ $message->id }}">
                            <div class="inline-block max-w-[80%] rounded-2xl px-4 py-2 {{ $message->sender_id === auth()->id() ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                                @if($message->replyTo)
                                    <p class="text-xs opacity-70 mb-1">Replying to {{ $message->replyTo->sender?->name }}: {{ \Illuminate\Support\Str::limit($message->replyTo->body, 80) }}</p>
                                @endif
                                <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                                <p class="mt-1 text-[11px] opacity-70">{{ $message->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-sm text-gray-500 mt-2" x-show="typingUsers.length > 0" x-cloak>
                    <span x-text="typingUsers.map(u => u.name).join(', ')"></span> typing...
                </p>

                <form method="POST" action="{{ route('messages.store', $conversation) }}" class="mt-4 space-y-2" @submit="isTyping = false">
                    @csrf
                    <input type="hidden" name="reply_to_message_id" x-model="replyToId">
                    <div x-show="replyToPreview" x-cloak class="text-xs text-gray-600 bg-gray-50 rounded-md px-3 py-2 flex justify-between">
                        <span x-text="replyToPreview"></span>
                        <button type="button" class="text-red-500" @click="replyToId = null; replyToPreview = null">clear</button>
                    </div>
                    <textarea
                        id="chat-input"
                        name="body"
                        rows="3"
                        maxlength="4000"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500"
                        placeholder="Type a message"
                        required
                        @input.debounce.400ms="notifyTyping()"
                    ></textarea>
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-500">Polling every 3 seconds</p>
                        <x-primary-button>Send</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function chatRoom(config) {
            return {
                pollUrl: config.pollUrl,
                typingUrl: config.typingUrl,
                csrfToken: config.csrfToken,
                lastId: config.initialLastId,
                typingUsers: [],
                replyToId: null,
                replyToPreview: null,
                init() {
                    this.scrollToBottom();
                    setInterval(() => this.poll(), 3000);
                },
                async poll() {
                    const response = await fetch(`${this.pollUrl}?after_id=${this.lastId}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) {
                        return;
                    }
                    const payload = await response.json();
                    this.typingUsers = payload.typing_users || [];
                    (payload.messages || []).forEach(message => this.appendMessage(message));
                },
                appendMessage(message) {
                    const list = document.getElementById('message-list');
                    if (!list || list.querySelector(`[data-message-id="${message.id}"]`)) {
                        return;
                    }
                    const mine = Number(message.sender_id) === Number({{ auth()->id() }});
                    const wrapper = document.createElement('div');
                    wrapper.className = mine ? 'text-right' : 'text-left';
                    wrapper.setAttribute('data-message-id', String(message.id));
                    wrapper.innerHTML = `<div class="inline-block max-w-[80%] rounded-2xl px-4 py-2 ${mine ? 'bg-pink-600 text-white' : 'bg-gray-100 text-gray-900'}"><p class="whitespace-pre-wrap break-words"></p><p class="mt-1 text-[11px] opacity-70"></p></div>`;
                    wrapper.querySelector('p').textContent = message.body;
                    wrapper.querySelectorAll('p')[1].textContent = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                    list.appendChild(wrapper);
                    this.lastId = Math.max(this.lastId, Number(message.id));
                    this.scrollToBottom();
                },
                scrollToBottom() {
                    const list = document.getElementById('message-list');
                    if (list) {
                        list.scrollTop = list.scrollHeight;
                    }
                },
                async notifyTyping() {
                    await fetch(this.typingUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ typing: true })
                    });
                }
            }
        }
    </script>
</x-app-layout>
