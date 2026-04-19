<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public const SETTING_KEYS = [
        'e2ee_enabled',
        'chat_history_enabled',
        'media_upload_enabled',
        'friend_only_messaging_enabled',
        'registration_enabled',
        'email_verification_enabled',
    ];

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings' => ['array'],
        ]);

        foreach (self::SETTING_KEYS as $key) {
            AdminSetting::setBool(
                $key,
                (bool) data_get($request->input('settings', []), $key, false),
                $request->user()->id
            );
        }

        return back()->with('status', 'settings-updated');
    }
}
