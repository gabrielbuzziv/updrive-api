<?php

namespace App\Console\Commands;

use App\Account;
use App\Dispatch;
use App\Document;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResendDocuments extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updrive:resend-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend documents without validity';

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
            setActiveAccount($account);

            $documents = (new Document())
                ->whereNull('validity')
                ->where('status', 2)
                ->whereDate('resent_at', '<=', Carbon::today()->subDays(7))
                ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
                ->get();

            $documents->each(function ($document) {
                if ($document->resents < 2) {
                    $document->sharedWith->each(function ($contact) use ($document) {
                        if ( ! isset($this->notifications[$document->company->id][$contact->id]['contact']))
                            $this->notifications[$document->company->id][$contact->id]['contact'] = $contact;

                        $this->notifications[$document->company->id][$contact->id]['documents'][] = $document;
                    });
                } else {
                    $document->status = 5;
                    $document->save();

                    $document->history()->create([
                        'action'      => 10,
                        'description' => 'Pausado após 3 tentativas de envio.'
                    ]);
                }
            });

            foreach ($this->notifications as $company => $notification) {
                foreach ($notification as $contact) {
                    $contact = (object) $contact;

                    $dispatch = Dispatch::create([
                        'company_id' => $company,
                        'subject'    => 'Você tem documentos pendentes',
                        'message'    => 'Parece que você não abriu alguns dos documentos que enviamos, abaixo seguem os documentos ainda pendentes.',
                    ]);

                    $dispatch->recipients()->attach($contact->contact->id);

                    foreach ($contact->documents as $document) {
                        $dispatch->documents()->attach($document->id);
                        $document->history()->create([
                            'user_id'     => $contact->contact->id,
                            'action'      => 6,
                            'description' => 'Notificação sobre documento pendente ainda não aberto.'
                        ]);
                        $document->resent_at = Carbon::now();
                        $document->resents = $document->resents + 1;
                        $document->save();
                    }

                    Mail::to($contact->contact->email)->send(new \App\Mail\ResendDocuments($dispatch, $contact->contact));
                }
            }

            $this->info($documents->count());
            $this->notifications = [];
        });
    }
}
