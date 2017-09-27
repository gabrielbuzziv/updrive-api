<?php

namespace App\Events;

use App\Document;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentStatusUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Document
     *
     * @var Document
     */
    public $document;

    /**
     * Account
     *
     * @var
     */
    public $account;

    /**
     * Create a new event instance.
     *
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->account  = config('account')->slug;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('updrive');
    }
}
