<?php

namespace BookStack\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StalePages extends Notification
{
    use Queueable;

    private $Pages = [];
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($Pages)
    {
        $this->Pages = $Pages;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $Message = (new MailMessage)
            ->subject("You have stale :appName pages", ['appName' => setting('app-name')])
            ->line('The following pages have not had a revision in more than :days', ['days' => config('ownerNotifications.staleDocumentThresholdDays')]);
        $Message = $this->addPageList($Message);
        return $Message->action(trans('auth.reset_password'), url('password/reset/' . $this->token))
            ->line('Please update these pages as soon as possible.');
    }

    private function addPageList($Message) {
        foreach($this->Pages as $Page) {
            $Message->action($Page->name, url(config('app.url') . '/link/' . $Page->id));
        }
        return $Message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
