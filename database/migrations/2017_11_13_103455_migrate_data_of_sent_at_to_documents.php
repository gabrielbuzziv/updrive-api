<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateDataOfSentAtToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $documents = DB::table('documents')->whereNull('resent_at')->get();

        $documents->each(function ($document) {
            DB::table('documents')->where('id', $document->id)->update(['resent_at' => $document->created_at]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $documents = DB::table('documents')->whereNotNull('sent_at')->get();

        $documents->each(function ($document) {
            DB::table('documents')->where('id', $document->id)->update(['resent_at' => null]);
        });
    }
}
