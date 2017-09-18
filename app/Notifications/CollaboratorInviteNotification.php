<?php

namespace App\Notifications;

use App\Illuminate\Notifications\UPMailMessage;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CollaboratorInviteNotification extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     *
     * @params User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
            ->subject('Você foi convidado para participar do UP Drive')
            ->line('Você acabou de ser convidado para a equipe de colaboradores, clique no link abaixo para aceitar o convite.')
            ->action('Aceitar o convite', sprintf('%s/registrar?email=%s', config('app.frontend'), $this->user->email))
            ->regards(config('account')->name)
            ->from(config('account')->email, config('account')->name);
    }
}
