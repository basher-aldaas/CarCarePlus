<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fire the appointment reminders every minute: customer 30 min before,
// assigned employee 1 hour before, each order's scheduled time.
Schedule::command('orders:send-reminders')->everyMinute()->withoutOverlapping();
