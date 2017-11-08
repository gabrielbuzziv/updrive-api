<?php

namespace App\Notifications;

use App\Dispatch;
use App\Events\EmailOpened;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Notifications\Notification;

class EmailOpenedNotification extends Notification
{

    use Transformable;

    /**
     * Dispatch
     *
     * @var Dispatch
     */
    protected $dispatch;

    /**
     * Contact
     *
     * @var User
     */
    protected $recipient;

    /**
     * Create a new notification instance.
     *
     * @param Dispatch $dispatch
     * @param User $recipient
     */
    public function __construct(Dispatch $dispatch, User $recipient)
    {
        $this->dispatch = $dispatch;
        $this->recipient = $recipient;

        event(new EmailOpened($this->dispatch, $this->recipient));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
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
            'subject'   => $this->dispatch->subject,
            'user'      => $this->transformItem($this->dispatch->sender, new UserTransformer()),
            'recipient' => $this->transformItem($this->recipient, new ContactTransformer())
        ];
    }
}
