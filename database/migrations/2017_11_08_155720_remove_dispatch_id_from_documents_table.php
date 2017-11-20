<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveDispatchIdFromDocumentsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['dispatch_id']);
            $table->dropColumn('dispatch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('dispatch_id')->unsigned()->nullable();
            $table->foreign('dispatch_id')->references('id')->on('documents_dispatch')->onDelete('cascade');
        });

        $documents = DB::table('dispatch_document')->get();
        $documents->each(function ($document) {
            DB::table('documents')
                ->where('id', $document->document_id)
                ->update(['dispatch_id' => $document->dispatch_id]);
        });
    }
}
