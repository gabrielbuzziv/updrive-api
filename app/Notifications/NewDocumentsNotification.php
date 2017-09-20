<?php

namespace App\Notifications;

use App\Account;
use App\DocumentDispatch;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tymon\JWTAuth\Facades\JWTAuth;

class NewDocumentsNotification extends Notification implements ShouldQueue
{

    use Queueable, Transformable;

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
     * @param $dispatchId
     * @param $contactId
     * @param $accountId
     */
    public function __construct($dispatchId, $contactId, $accountId)
    {
        $account = Account::find($accountId);
        setActiveAccount($account);

        $contact = User::find($contactId);
        $this->dispatch = DocumentDispatch::find($dispatchId);
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
            ->from($this->dispatch->user->email, $this->dispatch->user->name)
            ->subject($this->dispatch->subject)
            ->view('emails.documents', [
                'subject'       => $this->dispatch->subject,
                'description'   => $this->dispatch->message,
                'documents'     => $this->transformCollection($this->dispatch->documents, new DocumentTransformer()),
                'regards'       => $this->dispatch->user->name,
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', config('account')->slug),
                'frontend_url'  => config('app.frontend'),
                'footer'        => true,
            ]);
    }
}
