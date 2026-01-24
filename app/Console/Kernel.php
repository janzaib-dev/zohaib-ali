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
        // Pull attendance from biometric devices hourly
        // Pull attendance from biometric devices hourly
        $schedule->command('attendance:sync-from-device')->hourly();

        // Mark absent employees automatically every night at 11:00 PM
        $schedule->command('attendance:mark-absent')->dailyAt('23:00');
        
        // Sync device time daily to prevent drift
        $schedule->command('device:sync-time')->dailyAt('04:00');

        // Generate monthly payrolls on the 25th of every month
        $schedule->command('payroll:generate-monthly')->monthlyOn(25, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
