<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchesTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches_tracking', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dispatch_id')->unsigned();
            $table->integer('recipient_id')->unsigned();
            $table->string('status')->default('sent');
            $table->timestamps();

            $table->foreign('dispatch_id')->references('id')->on('dispatches')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        $trackings = DB::table('documents_dispatch_tracking')->orderBy('id', 'asc')->get();
        $trackings->each(function ($tracking) {
            DB::table('dispatches_tracking')->insert([
                'dispatch_id'  => $tracking->dispatch_id,
                'recipient_id' => $tracking->contact_id,
                'status'       => $tracking->status,
                'created_at'   => $tracking->created_at,
                'updated_at'   => $tracking->updated_at,
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
        Schema::dropIfExists('dispatches_tracking');
    }
}
