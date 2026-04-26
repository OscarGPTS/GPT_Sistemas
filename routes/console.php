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
