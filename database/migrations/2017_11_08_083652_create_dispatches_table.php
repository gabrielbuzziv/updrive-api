<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('sender_id')->unsigned()->nullable();
            $table->string('subject');
            $table->longText('message');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        $dispatches = DB::table('documents_dispatch')->orderBy('id', 'asc')->get();
        $dispatches->each(function ($dispatch) {
            DB::table('dispatches')->insert([
                'company_id' => $dispatch->company_id,
                'sender_id'  => $dispatch->user_id,
                'subject'    => $dispatch->subject,
                'message'    => $dispatch->message,
                'created_at' => $dispatch->created_at,
                'updated_at' => $dispatch->updated_at,
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
        Schema::dropIfExists('dispatches');
    }
}
