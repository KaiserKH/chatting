<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserConversationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
            'reply_to_message_id' => ['nullable', 'integer', 'exists:messages,id'],
        ]);

        $settings = UserConversationSetting::query()->firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $request->user()->id],
            ['save_history' => true, 'mute_notifications' => false, 'encryption_enabled' => false]
        );

        $adminAllowsHistory = AdminSetting::bool('chat_history_enabled', true);
        $expiresAt = (! $adminAllowsHistory || ! $settings->save_history)
            ? now()->addDay()
            : null;

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $request->user()->id,
            'reply_to_message_id' => $validated['reply_to_message_id'] ?? null,
            'body' => trim($validated['body']),
            'expires_at' => $expiresAt,
        ]);

        $conversation->participants()
            ->where('users.id', '!=', $request->user()->id)
            ->get()
            ->each(function ($participant) use ($message) {
                $message->reads()->updateOrCreate(
                    ['user_id' => $participant->id],
                    ['delivered_at' => now()]
                );
            });

        return redirect()->route('chat.show', $conversation);
    }

    public function destroyForMe(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $message->reads()->where('user_id', $request->user()->id)->delete();

        return back();
    }

    public function destroyForEveryone(Request $request, Message $message): RedirectResponse
    {
        abort_unless($message->sender_id === $request->user()->id, 403);

        $message->forceFill([
            'body' => '[deleted]',
            'is_deleted_for_everyone' => true,
        ])->save();

        return back();
    }

    public function clearConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $conversation->messages()->where('sender_id', $request->user()->id)->delete();

        return back()->with('status', 'conversation-cleared');
    }
}
