<?php

use App\Services\AttendanceStore;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:auto-mark-absent {--date=} ', function ($date = null) {
    $date = $date ?? now()->format('Y-m-d');
    $count = AttendanceStore::autoMarkAbsentForMissingDailyCheckIns($date);

    $this->info(sprintf('Auto-marked %d student(s) absent for %s.', $count, $date));
})->purpose('Mark any student without a check-in for the day as absent.');

Schedule::command('attendance:auto-mark-absent')->dailyAt('23:59');
