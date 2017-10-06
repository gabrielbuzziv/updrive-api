<?php

namespace App\Notifications;

use App\Document;
use App\Events\DocumentOpened;
use App\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;

class DocumentOpenedNotification extends Notification implements ShouldBroadcast
{

    /**
     *  Document
     *
     * @var Document
     */
    protected $document;

    /**
     * Contact
     *
     * @var User
     */
    protected $contact;

    /**
     * Create a new notification instance.
     *
     * @param Document $document
     * @param User $contact
     */
    public function __construct(Document $document, User $contact) {
        $this->document = $document;
        $this->contact = $contact;

        event(new DocumentOpened($this->document, $this->contact));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            'user'     => $this->contact,
            'document' => $this->document,
        ];
    }
}
