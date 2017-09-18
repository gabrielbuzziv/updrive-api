<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentDispatchContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_dispatch_contact', function (Blueprint $table) {
            $table->integer('dispatch_id')->unsigned();
            $table->integer('contact_id')->unsigned();

            $table->foreign('dispatch_id')->references('id')->on('documents_dispatch')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_dispatch_contact');
    }
}
