<?php

namespace BookStack\Console;

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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(
            function () {
                if (config('owner-notifications.ownerNotificationChannel') !== '' && config('owner-notifications.cron') !== '') {
                    $Timestamp = Carbon\Carbon::now()->subDays(config('owner-notifications.staleDocumentThresholdDays'))->toDateTimeString();
                    \BookStack\Entities\Models\Page::whereDosentHave('revisions', function ($query) use($Timestamp){
                        $query->where('updated_at','<',$Timestamp);
                    })->groupBy('created_by')
                        ->pluck('created_by')
                        ->each(function($UserId) use($Timestamp){
                            //distinct users with pages requiring updates.
                            $Pages = \BookStack\Entities\Models\Page::whereDosentHave('revisions', function ($query) use($Timestamp){
                                    $query->('updated_at', '>', $Timestamp);
                                })->select('owned_by, id, name')
                                ->get();
                            \BookStack\Auth\User::find($UserId)->notify(new StalePages($Pages));
                        });
                }
            }
        )
        ->name('stale_item_notification')
        ->cron(config('owner-notifications.cron'))
        ->withoutOverlapping()
        ->runInBackground()
        ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
