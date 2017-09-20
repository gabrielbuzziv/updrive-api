<?php

namespace App\Notifications;

use App\DocumentDispatch;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewDocumentsNotification extends Notification
{

    use  Transformable;

    /**
     * Attribute Dispatch
     *
     * @var DocumentDispatch
     */
    protected $dispatch;

    /**
     * Attribute Token
     *
     * @var
     */
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
        $this->token = JWTAuth::fromUser($contact);
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
        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS'), $this->dispatch->user->name)
            ->subject($this->dispatch->subject)
            ->view('emails.default', [
                'subject'       => $this->dispatch->subject,
                'description'   => $this->dispatch->message,
                'documents'     => $this->transformCollection($this->dispatch->documents, new DocumentTransformer()),
                'regards'       => [
                    'name'  => $this->dispatch->user->name,
                    'email' => $this->dispatch->user->email,
                ],
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', config('account')->slug),
                'frontend_url'  => config('app.frontend'),
                'footer'        => true,
            ]);
    }
}
