<?php

namespace App\Http\Controllers;

use App\Account;
use App\DocumentDispatch;
use App\DocumentDispatchTracking;
use App\Events\NewMailTracking;
use App\Notifications\EmailDeliveredNotification;
use App\Notifications\EmailOpenedNotification;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Tracking Deliveries.
     */
    public function trackingDeliveries()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $this->track('delivered', 'email_delivered');
        }
    }

    /**
     * Tracking Opened.
     */
    public function trackingOpened()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $this->track('opened', 'email_opened');
        }
    }

    /**
     * Tracking Opens.
     */
    public function trackingSpams()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $this->track('spam');
        }
    }

    /**
     * Tracking Opens.
     */
    public function trackingBounces()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $this->track('bounce');
        }
    }

    /**
     * Check if is a trackable data.
     *
     * @return bool
     */
    private function isTrackable()
    {
        return request()->get('account')
                && request()->get('dispatch')
                && request()->get('contact') ? true : false;
    }

    /**
     * Setup database.
     */
    private function setupDatabase()
    {
        DB::disconnect('account');
        setActiveAccount(Account::find(request()->get('account')));
    }

    /**
     * Track document.
     *
     * @param string $status
     * @param string $notification
     */
    private function track($status = 'sent', $notification = '')
    {
        $contactId = request()->get('contact');
        $dispatch = DocumentDispatch::find(request()->get('dispatch'));

        $dispatch->contacts->each(function ($contact) use ($contactId, $dispatch, $status, $notification) {
            if ($contact->id == $contactId) {
                $this->createTracking($dispatch, $contact, $status);

                event(new NewMailTracking());

                if (! empty($notification) && $dispatch->user->notificationsSettings->contains('notification', $notification)) {
                    switch ($notification) {
                        case 'email_delivered':
                            $dispatch->user->notify(new EmailDeliveredNotification($dispatch, $contact));
                            break;
                        case 'email_opened':
                            $dispatch->user->notify(new EmailOpenedNotification($dispatch, $contact));
                            break;
                    }
                }
            }
        });
    }

    /**
     * Create the document dispatch tracking register.
     *
     * @param DocumentDispatch $dispatch
     * @param User $contact
     * @param string $status
     */
    private function createTracking(DocumentDispatch $dispatch, User $contact, $status)
    {
        DocumentDispatchTracking::create([
            'dispatch_id' => $dispatch->id,
            'contact_id'  => $contact->id,
            'status'      => $status,
        ]);
    }
}
