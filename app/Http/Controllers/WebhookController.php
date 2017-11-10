<?php

namespace App\Http\Controllers;

use App\Account;
use App\Dispatch;
use App\DispatchTracking;
use App\Events\NewMailTracking;
use App\Notifications\EmailDeliveredNotification;
use App\Notifications\EmailOpenedNotification;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{

    /**
     * WebhookController constructor.
     */
    public function __construct()
    {
        Log::useDailyFiles(storage_path('/logs/deliveries.log'));
    }

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
    public function trackingRead()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $this->track('read', 'email_opened');
        }
    }

    /**
     * Tracking Bounces.
     */
    public function trackingBounces()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();
            $recipient = request('recipient');
            $this->track('failed', null, "A mensagem não foi entregue pois o e-mail {$recipient} não foi encontrado.");
        }
    }

    /**
     * Tracking Dropped.
     */
    public function trackingDropped()
    {
        if ($this->isTrackable()) {
            $this->setupDatabase();

            $description = request('reason') == 'hardfail'
                ? 'O envio para este destinatário já sofreu falha anteriormente, verifique se o endereço de e-mail está correto.'
                : 'Tentamos enviar o e-mail durante 8 horas, mas não foi possível fazer comunicação com o servidor do destinatário.';

            $this->track('failed', null, $description);
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
    private function track($status = 'sent', $notification = null, $description = null)
    {
        $recipientId = request()->get('contact');
        $dispatch = Dispatch::find(request()->get('dispatch'));

        $dispatch->recipients->each(function ($recipient) use ($recipientId, $dispatch, $status, $notification, $description) {
            if ($recipient->id == $recipientId) {

                Log::info([
                    'dispatch'  => $dispatch->id,
                    'recipient' => $recipient->id,
                    'status'    => $status
                ]);

                $this->createTracking($dispatch, $recipient, $status, $description);

                event(new NewMailTracking());

                if ( ! empty($notification) && $dispatch->sender && $dispatch->sender->notificationsSettings->contains('notification', $notification)) {
                    switch ($notification) {
                        case 'email_delivered':
                            $dispatch->sender->notify(new EmailDeliveredNotification($dispatch, $recipient));
                            break;
                        case 'email_opened':
                            $dispatch->sender->notify(new EmailOpenedNotification($dispatch, $recipient));
                            break;
                    }
                }
            }
        });
    }

    /**
     * Create the document dispatch tracking register.
     *
     * @param Dispatch $dispatch
     * @param User $recipient
     * @param $status
     */
    private function createTracking(Dispatch $dispatch, User $recipient, $status, $description = null)
    {
        DispatchTracking::create([
            'dispatch_id'  => $dispatch->id,
            'recipient_id' => $recipient->id,
            'status'       => $status,
        ]);

        $dispatch->documents->each(function ($document) use ($recipient, $status, $description) {
            $document->history()->create([
                'user_id'     => $recipient->id,
                'action'      => $this->getStatusId($status),
                'description' => $description,
            ]);
        });
    }

    /**
     * Convert the dispatch tracking status to history status id.
     *
     * @param $status
     * @return int
     */
    private function getStatusId($status)
    {
        switch ($status) {
            case 'sent':
                return 2;
            case 'delivered':
                return 7;
            case 'read':
                return 8;
        }
    }
}
