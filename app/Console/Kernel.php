<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clear expired tokens daily
        $schedule->command('tokens:clear')->daily();

        // Purge cache every hour
        $schedule->command('cache:prune-stale-tags')->hourly();

        // Clear failed jobs weekly
        $schedule->command('queue:failed-table')->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
