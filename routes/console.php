<?php

use App\Models\Room;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync room status every minute
Schedule::call(function () {
    Room::chunkById(100, function ($rooms) {
        foreach ($rooms as $room) {
            $room->syncStatus();
        }
    });
})->everyMinute()->name('rooms.sync-status')->withoutOverlapping();
