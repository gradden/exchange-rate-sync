<?php

namespace App\Console;

use App\Jobs\ExchangeRateSyncCurrentJob;
use App\Repositories\ExchangeRateRepository;
use App\Services\ExchangeRateService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        foreach (config('ecb.daily-cron-time') as $time) {
            $schedule->job(new ExchangeRateSyncCurrentJob(new ExchangeRateService(new ExchangeRateRepository())))
                ->timezone('Europe/Budapest')
                ->dailyAt($time);
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
