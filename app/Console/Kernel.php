<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AutoApproval::class,
        Commands\Reminder::class,
        Commands\CompletionMail::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('max_rev:hourly')->dailyAt('06:00');//runs daily at 6 am
        $schedule->command('brands:update')->hourly();//runs daily at 6 am
        $schedule->command('updateStockistMaster:update')->dailyAt('07:00');//runs daily at 6 am
        $schedule->command('institution_division_mapping:update')->dailyAt('07:00');//runs daily at 6 am

        $schedule->command('approval:daily')->dailyAt('05:00');//runs daily at 5am
        $schedule->command('failed_request:daily')->dailyAt('07:00');//runs daily at 7am

        // Schedule daily command to run every day at 7 AM
        $schedule->command('ceoapprovalmail_request daily')->dailyAt('07:00');
        // Schedule the command to run once a month on the same day at 7 AM
        $schedule->command("ceoapprovalmail_request monthly")->monthlyOn(1, '07:00');

        


        /*$schedule->command('reminder:cron')
        ->daily();

        $schedule->command('completion:cron')
        ->everyMinute();*/
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
