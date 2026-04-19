<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Conversation;
use App\Models\UserConversationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationSettingController extends Controller
{
    public function update(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'save_history' => ['nullable', 'boolean'],
            'mute_notifications' => ['nullable', 'boolean'],
            'encryption_enabled' => ['nullable', 'boolean'],
        ]);

        $allowEncryption = AdminSetting::bool('e2ee_enabled', false);

        UserConversationSetting::query()->updateOrCreate(
            [
                'conversation_id' => $conversation->id,
                'user_id' => $request->user()->id,
            ],
            [
                'save_history' => $request->boolean('save_history'),
                'mute_notifications' => $request->boolean('mute_notifications'),
                'encryption_enabled' => $allowEncryption && $request->boolean('encryption_enabled'),
            ]
        );

        return back()->with('status', 'conversation-settings-updated');
    }
}
