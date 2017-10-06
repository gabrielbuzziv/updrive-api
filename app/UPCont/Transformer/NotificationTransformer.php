<?php

namespace App\UPCont\Transformer;

use App\Document;
use App\Http\Controllers\Traits\Transformable;
use App\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class NotificationTransformer extends TransformerAbstract
{

    use Transformable;

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
    public function transform($notification)
    {
        return [
            'type' => $this->parseType($notification->type),
            'data' => $notification->data,
            'read' => (bool) $notification->read_at
        ];
    }

    /**
     * Parse notification Type
     *
     * @param $type
     * @return mixed
     */
    private function parseType($type)
    {
        $type = explode('App\\Notifications\\', $type);

        return str_replace('Notification', '', $type[1]);
    }
}