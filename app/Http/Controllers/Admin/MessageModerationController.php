<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageModerationController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(AdminSetting::bool('e2ee_enabled', false), 403, 'Message moderation is disabled while encryption is enabled.');

        $messages = Message::query()
            ->with(['sender', 'conversation'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('body', 'like', '%'.$request->string('q').'%');
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.messages', compact('messages'));
    }
}
