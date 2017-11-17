<?php

namespace App\Console\Commands;

use App\Account;
use App\Document;
use App\Notifications\DocumentExpiredNotification;
use App\Notifications\ExpiredDocumentsNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use mikehaertl\pdftk\Pdf;

class CheckExpiredDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updrive:expired-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if have expire documents and then add a watermark to them.';

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

            $documents = Document::whereNotNull('validity')
                ->where('validity', '<', Carbon::today())
                ->where('status', 2)
                ->get();

            $this->info("Foram encontrado {$documents->count()} documentos vencido.");

            $documents->each(function ($document) use ($account) {
                if ($document->status['id'] == 2) {
                    if (! isset($this->notifications[$document->user->id]['user'])) {
                        $this->notifications[$document->user->id]['user'] = $document->user;
                    }
                    $this->notifications[$document->user->id]['documents'][] = $document;

                    $this->info("O documento \"{$document->name}\" ainda não havia sido aberto.");
                }

                $document->status = 4;
                $document->save();

                $document->history()->create(['user_id' => null, 'action' => 5]);

                if ($document->type['ext'] == 'pdf') {
                    $local = Storage::disk('local');
                    $s3    = Storage::disk('s3');

                    $path = sprintf('%s/documents/%s', $account->slug, $document->filename);
                    $file = $s3->get($path);

                    $tmp = "tmp/{$document->filename}";
                    $storage = storage_path("app/{$tmp}");

                    $local->put("{$tmp}", $file);
                    $pdf = new Pdf($storage);
                    $pdf->stamp(url('watermark.pdf'))->saveAs($storage);
                    $watermarked = $local->get($tmp);
                    $s3->put($path, $watermarked);
                    $local->delete($tmp);
                }

                $this->info("O documento \"{$document->name}\" venceu.");
            });

            foreach ($this->notifications as $notification) {
                $notification = (object) $notification;

                if ($notification->user->notificationsSettings->contains('notification', 'document_expired')) {
                    $notification->user->notify(new DocumentExpiredNotification($notification));
                }
            }

            $this->info("Expired Documents check in account '{$account->name}' is complete.");
            $this->info("___________________________");
        });
    }
}
