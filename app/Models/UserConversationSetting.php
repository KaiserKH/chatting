<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConversationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'save_history',
        'mute_notifications',
        'encryption_enabled',
    ];

    protected function casts(): array
    {
        return [
            'save_history' => 'boolean',
            'mute_notifications' => 'boolean',
            'encryption_enabled' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
