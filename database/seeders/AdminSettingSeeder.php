<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'e2ee_enabled' => false,
            'chat_history_enabled' => true,
            'media_upload_enabled' => false,
            'friend_only_messaging_enabled' => false,
            'registration_enabled' => true,
            'email_verification_enabled' => false,
        ];

        foreach ($defaults as $key => $value) {
            AdminSetting::setBool($key, $value);
        }
    }
}
