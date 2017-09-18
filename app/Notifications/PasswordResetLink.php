<?php

namespace App\Notifications;

use App\Illuminate\Notifications\UPMailMessage;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetLink extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The attribute is a intance of User model.
     *
     * @var
     */
    protected $user;

    /**
     * The attribute is a string.
     *
     * @var
     */
    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
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
        $firstname = strtok($this->user->name, ' ');
        $url = url('/resetar-senha') . "?token={$this->token}&email={$this->user->email}";

        return (new UPMailMessage)
                    ->subject("{$firstname}, você solicitou uma nova senha?")
                    ->description('Esqueceu sua senha? Deixa que eu te ajudo')
                    ->success()
                    ->greeting("Oi {$firstname}")
                    ->line('Recebi uma solicitação de nova senha, foi você quem solicitou? caso deseje resetar a sua senha no <b>UP Cont</b> clique no botão abaixo.')
                    ->action('Escolher uma nova senha', $url)
                    ->line('<em>Não foi você quem solicitou? então pode ignore este e-mail.</em>')
                    ->regards('UP Cont');
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
