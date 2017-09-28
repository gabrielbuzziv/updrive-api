<?php

namespace App\Listeners;

use App\DocumentDispatch;
use App\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageSendingListener
{
    

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     *
     * @param  MessageSending  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        $message = $event->message;

        $headers = $message->getHeaders();
        $headers->addTextHeader('X-Mailgun-Variables', 'asd');
    }
}
