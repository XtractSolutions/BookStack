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
                \Log::info('----test log');
                if (config('ownerNotifications.ownerNotificationChannel') !== '' && config('ownerNotifications.cron') !== '') {
                    $Timestamp = Carbon\Carbon::now()->subDays(config('ownerNotifications.staleDocumentThresholdDays'))->toDateTimeString();
                    \BookStack\Entities\Models\Page::whereDosentHave('revisions', function ($query) use($Timestamp){
                        $query->where('updated_at','<',$Timestamp);
                    })->groupBy('created_by')
                        ->pluck('created_by')
                        ->each(function($UserId) use($Timestamp){
                            //distinct users with pages requiring updates.
                            $Pages = \BookStack\Entities\Models\Page::whereDosentHave('revisions', function ($query) use($Timestamp){
                                    $query->where('updated_at', '>', $Timestamp);
                                })->select('owned_by, id, name')
                                ->get();
                            \BookStack\Auth\User::find($UserId)->notify(new StalePages($Pages));
                        });
                }
            }
        )
        ->when(function () {
            return config('ownerNotifications.cron') !== '';
        })
        ->name('stale_item_notification')
        ->cron(config('ownerNotifications.cron'))
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
