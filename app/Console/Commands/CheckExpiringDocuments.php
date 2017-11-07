<?php

namespace App\Console\Commands;

use App\Account;
use App\Document;
use App\DocumentDispatchTracking;
use App\Notifications\ExpiringDocumentsNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $this->info("Expiring Documents check in account '{$account->name}' has started.");

            $documents = Document::where('status', 2)
                ->whereNotNull('validity')
                ->whereDate('validity', Carbon::today()->format('Y-m-d'))
                ->get();

            $this->info("Foram encontrado {$documents->count()} documentos com prazo de vencimento para hoje.");

            $documents->each(function ($document) {
                $this->info($document->name);

                $document->sharedWith->each(function ($contact) use ($document) {
                    if (! isset($this->notifications[$contact->id]['contact'])) {
                        $this->notifications[$contact->id]['contact'] = $contact;
                    }

                    $this->notifications[$contact->id]['documents'][] = $document;
                });
            });

            foreach ($this->notifications as $notification) {
                $notification = (object) $notification;
                $notification->contact->notify(new ExpiringDocumentsNotification($notification));

                foreach ($notification->documents as $document) {
                    $document->history()->create(['user_id' => $notification->contact->id, 'action' => 6]);
                }
            }

            $this->info("Expiring Documents check in account '{$account->name}' is complete.");
            $this->info("___________________________");
        });
    }
}
