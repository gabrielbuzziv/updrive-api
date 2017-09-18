<?php

namespace App\Console\Commands;

use App\Account;
use App\Document;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CheckDocumentsAboutToExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updrive:teste';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
            setActiveAccount($account);
            $this->info("Expired Documents check in account '{$account->name}' has started.");

            DB::connection('account');

            $documents = Document::whereNotNull('validity')
                ->where('validity', '<', Carbon::today())
                ->where('status', '<>', 4)
                ->get();
//            Document::clearCache();

            $this->info("Foram encontrado {$documents->count()} documentos vencido.");
        });
    }
}
