<?php

namespace App\Events;

use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\DocumentTransformer;
use App\UPCont\Transformer\UserTransformer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocumentExpired implements ShouldBroadcast
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
     * @param $documents
     * @param $user
     */
    public function __construct($documents, $user)
    {
        $this->account = config('account')->slug;
        $this->type = 'DocumentExpired';
        $this->data = [
            'user'      => $this->transformItem($user, new UserTransformer()),
            'documents' => $this->transformCollection($documents, new DocumentTransformer()),
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
