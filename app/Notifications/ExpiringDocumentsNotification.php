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

class ExpiringDocumentsNotification extends Notification
{

    use Transformable;

    /**
     * The attribute documents.
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
     * Days.
     *
     * @var
     */
    protected $days;

    /**
     * Create a new notification instance.
     *
     * ExpiringDocumentsNotification constructor.
     * @param $data
     * @param $days
     */
    public function __construct($data, $days)
    {
        $this->documents = $this->transformCollection($data->documents, new DocumentTransformer());
        $this->token = JWTAuth::fromUser($data->contact);
        $this->days = $days;
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
        $subject = $this->getData()->subject;
        $account = config('account');

        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS'), $account->name)
            ->subject(sprintf('%s', $account->name, $subject))
            ->view('emails.default', [
                'subject'       => $subject,
                'description'   => $this->getData()->description,
                'documents'     => $this->documents,
                'regards'       => [
                    'name'  => $account->name,
                    'email' => $account->email,
                ],
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', $account->slug),
                'frontend_url'  => config('app.frontend'),
                'footer'        => true,
            ]);
    }
}
