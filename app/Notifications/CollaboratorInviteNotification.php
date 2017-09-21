<?php

namespace App\Notifications;

use App\User;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CollaboratorInviteNotification extends Notification
{

    /**
     * Attribute User
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $subject = 'Você foi convidado para participar do UP Drive';
        $account = config('account');
        $front_url = config('app.frontend');
        $token = md5($this->user->email);

        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS'), $account->name)
            ->subject($subject)
            ->view('emails.default', [
                'subject'       => $subject,
                'description'   => "Você foi convidado para participar da equipe de colaboradores da {$account->name}, clique no botão abaixo para aceitar e finalizar seu registro.",
                'action_button' => [
                    'href' => "{$front_url}/registrar?email={$this->user->email}&token={$token}",
                    'text' => 'Aceitar o convite',
                ],
                'regards'       => [
                    'name'  => $account->name,
                    'email' => $account->email,
                ],
            ]);
    }
}
