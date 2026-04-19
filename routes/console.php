<?php

use App\Models\Message;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:cleanup-expired-messages', function () {
    $count = Message::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->delete();

    $this->info("Deleted {$count} expired messages.");
})->purpose('Delete messages that reached expiration time.');

Artisan::command('chat:cleanup-unused-media', function () {
    $this->info('Phase 2 media cleanup command placeholder. No media schema is active yet.');
})->purpose('Cleanup unused media files (implemented in Phase 2).');

Schedule::command('chat:cleanup-expired-messages')->dailyAt('02:10');
Schedule::command('chat:cleanup-unused-media')->dailyAt('02:30');
