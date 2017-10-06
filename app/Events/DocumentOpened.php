<?php

namespace App\Events;

use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\ContactTransformer;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentOpened implements ShouldBroadcast
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
     * @param Document $document
     * @param User $contact
     */
    public function __construct(Document $document, User $contact)
    {
        $this->account = config('account')->slug;
        $this->type = 'DocumentOpened';
        $this->data = [
            'user'     => $this->transformItem($document->user, new UserTransformer()),
            'document' => $this->transformItem($document, new DocumentTransformer()),
            'contact'  => $this->transformItem($contact, new ContactTransformer())
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
