<?php

namespace App\Events;

use App\Dispatch;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailDelivered implements ShouldBroadcast
{

    use InteractsWithSockets, SerializesModels, Transformable;

    /**
     * Account
     *
     * @var
     */
    public $account;

    /**
     * Type
     *
     * @var
     */
    public $type;

    /**
     * Data
     *
     * @var
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param Dispatch $dispatch
     * @param User $recipient
     */
    public function __construct(Dispatch $dispatch, User $recipient)
    {
        $this->account = config('account')->slug;
        $this->type = 'EmailDelivered';
        $this->data = [
            'subject'   => $dispatch->subject,
            'user'      => $this->transformItem($dispatch->sender, new UserTransformer()),
            'recipient' => $this->transformItem($recipient, new ContactTransformer()),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('notifications');
    }
}
