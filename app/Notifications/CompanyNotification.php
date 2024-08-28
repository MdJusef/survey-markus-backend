<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyNotification extends Notification
{
    use Queueable;

    protected $image,$name,$message,$time,$data,$isGlobal;
    public function __construct($image,$name,$message,$time ,$data,$isGlobal)
    {
        $this->image = $image;
        $this->name = $name;
        $this->message = $message;
        $this->time = $time;
        $this->data = $data;
        $this->isGlobal = $isGlobal;
    }


    public function via(object $notifiable): array
    {
        return ['database'];
    }

//    public function toMail(object $notifiable): MailMessage
//    {
//        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
//    }

    public function toArray(object $notifiable): array
    {
        return [
            'image' => $this->image,
            'name' => $this->name,
            'message' => $this->message,
            'time' => $this->time,
//            'data' => $this->data,
            'isGlobal' => $this->isGlobal,
        ];
    }
}
