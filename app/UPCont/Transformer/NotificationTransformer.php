<?php

namespace App\UPCont\Transformer;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{

    /**
     * The attribute set the default fields to include.
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * The attribute set the available fields to include.
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * Notification default transformation.
     *
     * @param array $notification
     * @return array
     */
    public function transform(array $notification)
    {
        $notification = (object) $notification;

        return [
            'data' => $notification->data,
            'read' => (bool) $notification->read_at,
            'notified_at' => Carbon::createFromFormat('Y-m-d H:i:s', $notification->created_at)->diffForHumans(),
        ];
    }
}