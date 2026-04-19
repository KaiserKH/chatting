<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Models\UserConversationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with(['participants.profile'])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                    ->whereDoesntHave('reads', function ($readQuery) use ($user) {
                        $readQuery->where('user_id', $user->id)->whereNotNull('seen_at');
                    });
            }])
            ->orderByDesc(
                Message::query()
                    ->select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest('id')
                    ->limit(1)
            )
            ->get();

        return view('chat.index', [
            'conversations' => $conversations,
            'users' => User::query()->where('id', '!=', $user->id)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeConversation($conversation, $request->user()->id);

        $messages = $conversation->messages()
            ->with(['sender.profile', 'replyTo.sender'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $messages->setCollection($messages->getCollection()->reverse()->values());

        $this->markSeen($conversation, $request->user()->id);

        $settings = UserConversationSetting::query()->firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $request->user()->id],
            ['save_history' => true, 'mute_notifications' => false, 'encryption_enabled' => false]
        );

        return view('chat.show', [
            'conversation' => $conversation->load('participants.profile'),
            'messages' => $messages,
            'settings' => $settings,
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id', 'different:'.(string) $request->user()->id],
        ]);

        $otherUserId = (int) $validated['user_id'];

        $existingConversationId = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $request->user()->id))
            ->whereHas('participants', fn ($query) => $query->where('users.id', $otherUserId))
            ->has('participants', '=', 2)
            ->value('id');

        if ($existingConversationId) {
            return redirect()->route('chat.show', $existingConversationId);
        }

        $conversation = Conversation::query()->create([
            'created_by' => $request->user()->id,
        ]);

        $conversation->participants()->attach([
            $request->user()->id => ['joined_at' => now()],
            $otherUserId => ['joined_at' => now()],
        ]);

        UserConversationSetting::query()->insert([
            [
                'conversation_id' => $conversation->id,
                'user_id' => $request->user()->id,
                'save_history' => true,
                'mute_notifications' => false,
                'encryption_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => $conversation->id,
                'user_id' => $otherUserId,
                'save_history' => true,
                'mute_notifications' => false,
                'encryption_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation, $request->user()->id);

        $afterId = (int) $request->integer('after_id', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->with(['sender.profile', 'replyTo.sender'])
            ->orderBy('id')
            ->get();

        $this->markSeen($conversation, $request->user()->id);

        $typingUsers = collect($conversation->participants)
            ->reject(fn ($participant) => $participant->id === $request->user()->id)
            ->filter(function ($participant) use ($conversation) {
                return cache()->has('typing:'.$conversation->id.':'.$participant->id);
            })
            ->values()
            ->map(fn ($participant) => [
                'id' => $participant->id,
                'name' => $participant->name,
            ]);

        return response()->json([
            'messages' => $messages,
            'typing_users' => $typingUsers,
        ]);
    }

    private function authorizeConversation(Conversation $conversation, int $userId): void
    {
        abort_unless($conversation->participants()->where('users.id', $userId)->exists(), 403);
    }

    private function markSeen(Conversation $conversation, int $userId): void
    {
        $messageIds = $conversation->messages()->where('sender_id', '!=', $userId)->pluck('id');

        foreach ($messageIds as $messageId) {
            MessageRead::query()->updateOrCreate(
                ['message_id' => $messageId, 'user_id' => $userId],
                ['delivered_at' => now(), 'seen_at' => now()]
            );
        }

        $latestId = $conversation->messages()->max('id');

        if ($latestId) {
            $conversation->participants()->updateExistingPivot($userId, [
                'last_read_message_id' => $latestId,
            ]);
        }
    }
}
