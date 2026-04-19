<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->boolean('is_admin')->default(false)->after('password');
            $table->boolean('is_banned')->default(false)->after('is_admin');
            $table->timestamp('last_seen_at')->nullable()->after('remember_token');
            $table->boolean('hide_online_status')->default(false)->after('last_seen_at');
            $table->boolean('hide_last_seen')->default(false)->after('hide_online_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'is_admin',
                'is_banned',
                'last_seen_at',
                'hide_online_status',
                'hide_last_seen',
            ]);
        });
    }
};
