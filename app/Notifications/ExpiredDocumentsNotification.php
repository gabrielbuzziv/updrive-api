<?php

namespace App\Notifications;

use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExpiredDocumentsNotification extends Notification
{

    use Transformable;

    /**
     * The attribute document.
     *
     * @var $this
     */
    protected $documents;

    /**
     * The attribute token.
     *
     * @var
     */
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
        $this->documents = $this->transformCollection($notification->documents, new DocumentTransformer());
        $this->token = JWTAuth::fromUser($notification->user);
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
        $subject = 'Alguns dos documentos que você enviou venceram sem ser abertos';
        $account = config('account');

        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS'), $account->name)
            ->subject($subject)
            ->view('emails.default', [
                'subject'     => $subject,
                'description' => 'Identificamos que alguns dos documentos que você enviou nos últimos dias não foram visualizados antes do prazo de vencimento. Abaixo está a lista dos documentos vencidos.',
                'documents'   => $this->documents,
                'regards'     => [
                    'name'  => $account->name,
                    'email' => $account->email,
                ],
                'token' => $this->token,
                'authorize_url' => action('AuthController@refreshToken', $account->slug),
            ]);
    }
}
