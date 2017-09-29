<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMailTracking implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    /**
     * Account
     *
     * @var
     */
    public $account;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->account  = config('account')->slug;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('tracking');
    }
}
