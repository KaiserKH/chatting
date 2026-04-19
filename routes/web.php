<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MessageModerationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationSettingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TypingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('chat.index');
    }

    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('chat.index');
})->middleware(['auth', 'not_banned', 'verify_if_enabled'])->name('dashboard');

Route::middleware(['auth', 'not_banned', 'verify_if_enabled'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/{conversation}/poll', [ChatController::class, 'poll'])->name('chat.poll');

    Route::post('/chat/{conversation}/messages', [MessageController::class, 'store'])->middleware('throttle:30,1')->name('messages.store');
    Route::delete('/messages/{message}/for-me', [MessageController::class, 'destroyForMe'])->name('messages.destroyForMe');
    Route::delete('/messages/{message}/for-everyone', [MessageController::class, 'destroyForEveryone'])->name('messages.destroyForEveryone');
    Route::delete('/chat/{conversation}/clear', [MessageController::class, 'clearConversation'])->name('chat.clear');

    Route::patch('/chat/{conversation}/settings', [ConversationSettingController::class, 'update'])->name('chat.settings.update');
    Route::post('/chat/{conversation}/typing', [TypingController::class, 'update'])->name('chat.typing');
});

Route::middleware(['auth', 'not_banned', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/ban', [UserManagementController::class, 'ban'])->name('users.ban');
    Route::patch('/users/{user}/unban', [UserManagementController::class, 'unban'])->name('users.unban');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/messages', [MessageModerationController::class, 'index'])->name('messages.index');
});

require __DIR__.'/auth.php';
