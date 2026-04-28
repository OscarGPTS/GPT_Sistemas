<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily 7:00 AM: send maintenance/warranty alerts
Schedule::command('gpt:send-alerts')
    ->dailyAt('07:00')
    ->onOneServer()
    ->withoutOverlapping();

// Weekly Monday 8:00 AM: notify about stale passwords (>90 days)
Schedule::command('gpt:check-stale-passwords')
    ->weeklyOn(1, '08:00')
    ->onOneServer()
    ->withoutOverlapping();
