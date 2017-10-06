<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\NotificationTransformer;

class NotificationController extends ApiController
{

    use Transformable;

    /**
     * Get all user unread notifications.
     * 
     * @return mixed
     */
    public function unread()
    {
        $notifications = auth()->user()->unreadNotifications;

        return $this->respond($this->transformCollection($notifications, new NotificationTransformer()));
    }

    /**
     * Read all unread notifications.
     *
     * @return mixed
     */
    public function read()
    {
        $notifications = auth()->user()->unreadNotifications->markAsRead();

        return $this->respond(['notifications' => $notifications]);
    }
}
