<?php

namespace App\Notifications;

use App\DocumentDispatch;
use App\Http\Controllers\Traits\Transformable;
use App\Illuminate\Notifications\UPMailMessage;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewDocumentsNotification extends Notification implements ShouldQueue
{
    use Queueable, Transformable;

    protected $dispatch;
    protected $contact;
    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param DocumentDispatch $dispatch
     * @param User $contact
     */
    public function __construct(DocumentDispatch $dispatch, User $contact)
    {
        $this->dispatch = $dispatch;
        $this->contact = $contact;
        $this->token = JWTAuth::fromUser($this->contact);
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
        return (new UPMailMessage())
            ->token($this->token)
            ->subject($this->dispatch->subject)
            ->line(nl2br($this->dispatch->message))
            ->documents($this->transformCollection($this->dispatch->documents, new DocumentTransformer()))
            ->regards($this->dispatch->user->name)
            ->from($this->dispatch->user->email, $this->dispatch->user->name);
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
