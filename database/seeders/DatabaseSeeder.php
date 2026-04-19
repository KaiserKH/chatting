<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminSettingSeeder::class);

        $admin = User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        Profile::query()->updateOrCreate(
            ['user_id' => $admin->id],
            ['status_message' => 'Administrator', 'bio' => 'Admin account']
        );

        $user = User::query()->updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        Profile::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['status_message' => 'Hey there! I am using Chatting.', 'bio' => 'Test profile']
        );
    }
}
