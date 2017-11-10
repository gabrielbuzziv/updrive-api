<?php

namespace App\Console\Commands;

use App\Account;
use App\Dispatch;
use App\Document;
use App\DocumentDispatchTracking;
use App\Mail\ExpiringDocuments;
use App\Notifications\ExpiringDocumentsNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckExpiringDocuments extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updrive:expiring-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if have documents about to expire, then notify users about it.';

    /**
     * The required notifications about expired documents that weren't visualized..
     *
     * @var array
     */
    protected $notifications = [];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = Account::all();

        $accounts->each(function ($account) {
            DB::disconnect('account');
            $this->notifications = [];
            setActiveAccount($account);
            $this->info($account->name);

            $this->resendDocumentsExpiringIn(0);
            $this->resendDocumentsExpiringIn(1);
            $this->resendDocumentsExpiringIn(2);


            $this->info("___________________________");
        });
    }

    /**
     * Resend notification if document will expire in 0, 1, 2 next days.
     *
     * @param int $days
     */
    private function resendDocumentsExpiringIn($days = 0)
    {
        $this->notifications = [];
        $documents = $this->getExpiringDocuments($days);
        $this->info("{$documents->count()} documentos encontrados que vencem em {$days} dias.");

        $documents->each(function ($document) {
            $document->sharedWith->each(function ($contact) use ($document) {
                if ( ! isset($this->notifications[$document->company->id][$contact->id]['contact']))
                    $this->notifications[$document->company->id][$contact->id]['contact'] = $contact;

                $this->notifications[$document->company->id][$contact->id]['documents'][] = $document;
            });
        });

        foreach ($this->notifications as $company => $notification) {
            foreach ($notification as $contact) {
                $contact = (object) $contact;

                $dispatch = Dispatch::create([
                    'company_id' => $company,
                    'subject'    => $this->getInfo($days)->subject,
                    'message'    => $this->getInfo($days)->message,
                ]);

                $dispatch->recipients()->attach($contact->contact->id);

                foreach ($contact->documents as $document) {
                    $dispatch->documents()->attach($document->id);
                    $document->history()->create([
                        'user_id' => $contact->contact->id,
                        'action' => 6,
                        'description' => $this->getInfo($days)->info
                    ]);
                }

                Mail::to($contact->contact->email)->send(new ExpiringDocuments($dispatch, $contact->contact));
            }
        }
    }

    /**
     * Get expire documents with days ahead.
     *
     * @param int $addDays
     * @return mixed
     */
    private function getExpiringDocuments($addDays = 0)
    {
        return Document::where('status', 2)
            ->whereNotNull('validity')
            ->whereDate('validity', Carbon::today()->addDays($addDays)->format('Y-m-d'))
            ->get();
    }

    /**
     * Get info based in days left to expire.
     *
     * @param $days
     * @return object
     */
    private function getInfo($days)
    {
        switch ($days) {
            case 0:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem hoje.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento para hoje.',
                    'info'        => 'Notificação sobre prazo do vencimento do documento expirando hoje.'
                ];
            case 1:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem amanhã.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento para amanhã.',
                    'info'        => 'Notificação sobre prazo do vencimento do documento expirando em 1 dia.'
                ];
            case 2:
                return (object) [
                    'subject'     => 'Você tem documentos que vencem em 2 dias.',
                    'description' => 'Identificamos que alguns documentos que enviamos para você ainda não foram baixados, evite perder a data de vencimento. Abaixo está os documentos com vencimento em 2 dias.',
                    'info'        => 'Notificação sobre prazo do vencimento do documento expirando em 2 dias.'
                ];
        }
    }
}
