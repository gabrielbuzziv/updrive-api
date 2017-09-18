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

class ExpiringDocumentsNotification extends Notification implements ShouldQueue
{

    use Queueable, Transformable;

    protected $contact;
    protected $documents;
    protected $token;

    /**
     * Create a new notification instance.
     *
     * ExpiringDocumentsNotification constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->contact = $data->contact;
        $this->documents = $this->transformCollection($data->documents, new DocumentTransformer());
        $this->token = JWTAuth::fromUser($this->contact);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $amount = count($this->documents);

        return (new UPMailMessage())
            ->subject("Você tem {$amount} documento(s) com vencimento para hoje.")
            ->line("Parece que há {$amount} documento(s) que ainda não foram baixados e vencerão hoje, estamos disponibilizando abaixo para você.")
            ->token($this->token)
            ->documents($this->documents)
            ->regards(config('account')->name)
            ->from(config('account')->email, config('account')->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
