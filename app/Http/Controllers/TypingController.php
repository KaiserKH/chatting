<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function update(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->participants()->where('users.id', $request->user()->id)->exists(), 403);

        cache()->put('typing:'.$conversation->id.':'.$request->user()->id, true, now()->addSeconds(10));

        return response()->json(['ok' => true]);
    }
}
