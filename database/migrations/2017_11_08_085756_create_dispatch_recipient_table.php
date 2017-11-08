<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchRecipientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_recipient', function (Blueprint $table) {
            $table->integer('dispatch_id')->unsigned();
            $table->integer('recipient_id')->unsigned();

            $table->foreign('dispatch_id')->references('id')->on('dispatches')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        $recipients = DB::table('document_dispatch_contact')->get();
        $recipients->each(function ($recipient) {
            DB::table('dispatch_recipient')->insert([
                'dispatch_id'  => $recipient->dispatch_id,
                'recipient_id' => $recipient->contact_id,
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
        Schema::dropIfExists('dispatch_recipient');
    }
}
