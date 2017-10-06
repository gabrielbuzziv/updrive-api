<?php

namespace App\Events;

use App\Document;
use App\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentOpened implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Document
     *
     * @var Document
     */
    public $document;

    /**
     * Contact
     *
     * @var User
     */
    public $contact;

    /**
     * Create a new event instance.
     *
     * @param Document $document
     * @param User $contact
     */
    public function __construct(Document $document, User $contact)
    {
        $this->document = $document;
        $this->contact = $contact;
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
