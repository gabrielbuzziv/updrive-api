<?php

namespace App\Notifications;

use App\Http\Controllers\Traits\Transformable;
use App\Illuminate\Notifications\UPMailMessage;
use App\UPCont\Transformer\DocumentTransformer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExpiredDocumentsNotification extends Notification implements ShouldQueue
{
    use Queueable, Transformable;

    protected $user;
    protected $documents;
    protected $token;

    /**
     *
     * Create a new notification instance.
     *
     * ExpiredDocumentsNotification constructor.
     * @param $notification
     */
    public function __construct($notification)
    {
        $this->user = $notification->user;
        $this->documents = $this->transformCollection($notification->documents, new DocumentTransformer());
        $this->token = JWTAuth::fromUser($this->user);
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
        $amount = count($this->documents);

        return (new UPMailMessage())
            ->subject("{$amount} documento(s) que você enviou venceram sem ser aberto.")
            ->line("Identificamos que {$amount} documento(s) venceram sem ser abertos, estou disponibilizando os documentos abaixo.")
            ->documents($this->documents)
            ->token($this->token)
            ->regards(config('account')->name)
            ->from(config('account')->email, config('account')->name);
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
