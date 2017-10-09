<?php

namespace App\Notifications;

use App\Document;
use App\Events\DocumentExpired;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\UserTransformer;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Tymon\JWTAuth\Facades\JWTAuth;

class DocumentExpiredNotification extends Notification
{

    use Transformable;

    /**
     *  Documents
     *
     * @var Document
     */
    protected $documents;

    /**
     * User
     *
     * @var
     */
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->documents = $data->documents;
        $this->user = $data->user;
        $this->token = JWTAuth::fromUser($data->user);

        event(new DocumentExpired($this->documents, $this->user));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $documents = $this->transformCollection($this->documents, new DocumentTransformer());
        $subject = 'Alguns dos documentos que você enviou venceram sem ser abertos';
        $account = config('account');

        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS'), $account->name)
            ->subject($subject)
            ->view('emails.default', [
                'subject'       => $subject,
                'description'   => 'Identificamos que alguns dos documentos que você enviou nos últimos dias não foram visualizados antes do prazo de vencimento. Abaixo está a lista dos documentos vencidos.',
                'documents'     => $documents,
                'regards'       => [
                    'name'  => $account->name,
                    'email' => $account->email,
                ],
                'token'         => $this->token,
                'authorize_url' => action('AuthController@refreshToken', $account->slug),
            ]);
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
            'user'      => $this->transformItem($this->user, new UserTransformer()),
            'documents' => $this->transformCollection($this->documents, new DocumentTransformer())
        ];
    }
}
