<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateOneMonthOlderDocumentsStatusToPaused extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $documents = DB::table('documents')->where('status', 2)->whereDate('created_at', '<', Carbon::today()->subDays(30))->get();
        $documents->each(function ($document) {
            DB::table('documents')->where('id', $document->id)->update(['status' => 5]);
            DB::table('documents_history')->insert(['document_id' => $document->id, 'action' => 10, 'created_at' => Carbon::now()]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $documents = DB::table('documents')->where('status', 5)->whereDate('created_at', '<', Carbon::today()->subDays(30))->get();
        $documents->each(function ($document) {
            DB::table('documents')->where('id', $document->id)->update(['status' => 2]);
            DB::table('documents_history')->where('document_id', $document->id)->where('action', 10)->delete();
        });
    }
}
