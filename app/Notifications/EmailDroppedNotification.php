<?php

namespace App\Notifications;

use App\DocumentDispatch;
use App\Events\EmailDropped;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailDroppedNotification extends Notification
{
    use Transformable;

    /**
     * Dispatch
     *
     * @var DocumentDispatch
     */
    protected $dispatch;

    /**
     * Contact
     *
     * @var User
     */
    protected $contact;

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

        event(new EmailDropped($this->dispatch, $this->contact));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'subject' => $this->dispatch->subject,
            'user'    => $this->transformItem($this->dispatch->user, new UserTransformer()),
            'contact' => $this->transformItem($this->contact, new ContactTransformer())
        ];
    }
}
