<?php

namespace BookStack\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use BookStack\Notifications\StalePages;
use Carbon\Carbon;

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
        if(config('ownerNotifications.cron') !== '') {
            $schedule->call(
                function () {
                    if (config('ownerNotifications.ownerNotificationChannel') !== '' && config('ownerNotifications.cron') !== '') {
                        $Timestamp = Carbon::now()->subDays(config('ownerNotifications.staleDocumentThresholdDays'))->toDateTimeString();
                        \BookStack\Entities\Models\Page::whereDoesntHave('revisions', function ($query) use($Timestamp){
                            $query->where('updated_at','<',$Timestamp);
                        })->groupBy('created_by')
                            ->pluck('created_by')
                            ->each(function($UserId) use($Timestamp){
                                //distinct users with pages requiring updates.
                                $Pages = \BookStack\Entities\Models\Page::whereDoesntHave('revisions', function ($query) use($Timestamp){
                                        $query->where('updated_at', '>', $Timestamp);
                                    })->where('owned_by', $UserId)
                                    ->select(['owned_by', 'id', 'name'])
                                    ->get();
                                $User = \BookStack\Auth\User::find($UserId);
                                if($User->name === 'Andrew Herren') {
                                    \Log::info('Notifying '.$User->name.' about '.sizeOf($Pages->toArray()).' pages');
                                    $User->notify(new StalePages($Pages));
                                } else {
                                    \Log::info('Pretend notifying '.$User->name.' about '.sizeOf($Pages->toArray()).' pages');
                                }
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
