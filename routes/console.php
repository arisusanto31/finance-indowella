<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:fix-problem-saldo 1')->cron('*/11 * * * *');
Schedule::command('app:fix-problem-saldo 2')->cron('*/9 * * * *');
Schedule::command('app:recalculate-kartu-stock-request')->cron('* * * * *');
Schedule::command('fill:index-date-invoice-sale')->dailyAt('21:00');
Schedule::command('app:fill-link-kartu-stock 2')->cron('*/5 * * * *');
Schedule::command('app:fill-lawan-journal-id')->hourly();