<?php

namespace App\Events;

use App\DocumentDispatch;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EmailDropped implements ShouldBroadcast
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
     * @param DocumentDispatch $dispatch
     * @param User $contact
     */
    public function __construct(DocumentDispatch $dispatch, User $contact)
    {
        $this->account = config('account')->slug;
        $this->type = 'EmailDropped';
        $this->data = [
            'subject' => $dispatch->subject,
            'user'    => $this->transformItem($dispatch->user, new UserTransformer()),
            'contact' => $this->transformItem($contact, new ContactTransformer()),
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
