<?php

namespace App\Events;

use App\DocumentDispatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MailTrackingUpdate implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Dispatch
     *
     * @var
     */
    public $dispatch;

    /**
     * Create a new event instance.
     *
     * @params DocumentDispatch $dispatch
     */
    public function __construct(DocumentDispatch $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('updrive');
    }
}
