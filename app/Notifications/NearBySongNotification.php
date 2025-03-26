<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Channels\FirebaseChannel;

class NearbySongNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $deviceToken;

    public function __construct($message, $deviceToken)
    {
        $this->message = $message;
        $this->deviceToken = $deviceToken;
    }

    public function via($notifiable)
    {
        return ['database', FirebaseChannel::class];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'token' => $this->deviceToken,
            'title' => 'Nearby Audio',
            'body'  => $this->message,
        ];
    }
}
