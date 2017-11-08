<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropDocumentDispatchTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('documents_dispatch_tracking');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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

        $trackings = DB::table('dispatches_tracking')->orderBy('id', 'asc')->get();
        $trackings->each(function ($tracking) {
            DB::table('documents_dispatch_tracking')->insert([
                'dispatch_id' => $tracking->dispatch_id,
                'contact_id'  => $tracking->recipient_id,
                'status'      => $tracking->status,
                'created_at'  => $tracking->created_at,
                'updated_at'  => $tracking->updated_at,
            ]);
        });
    }
}
