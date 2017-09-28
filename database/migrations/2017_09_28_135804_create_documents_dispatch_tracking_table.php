<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsDispatchTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_dispatch_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dispatch_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('status')->default('queue');
            $table->timestamps();

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
        Schema::dropIfExists('documents_dispatch_tracking');
    }
}
