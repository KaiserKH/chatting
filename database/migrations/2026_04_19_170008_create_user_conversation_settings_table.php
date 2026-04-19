<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_conversation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('save_history')->default(true);
            $table->boolean('mute_notifications')->default(false);
            $table->boolean('encryption_enabled')->default(false);
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_conversation_settings');
    }
};
