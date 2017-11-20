<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchDocumentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_document', function (Blueprint $table) {
            $table->integer('document_id')->unsigned();
            $table->integer('dispatch_id')->unsigned();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('dispatch_id')->references('id')->on('dispatches')->onDelete('cascade');
        });

        $documents = DB::table('documents')->whereNotNull('dispatch_id')->get();
        $documents->each(function ($document) {
            DB::table('dispatch_document')->insert([
                'dispatch_id'  => $document->dispatch_id,
                'document_id' => $document->id,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispatch_document');
    }
}
