<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropDocumentsDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('documents_dispatch');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('documents_dispatch', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('subject')->nullable();
            $table->longText('message');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $dispatches = DB::table('dispatches')->orderBy('id', 'asc')->get();
        $dispatches->each(function ($dispatch) {
            DB::table('documents_dispatch')->insert([
                'company_id' => $dispatch->company_id,
                'user_id'    => $dispatch->sender_id,
                'subject'    => $dispatch->subject,
                'message'    => $dispatch->message,
                'created_at' => $dispatch->created_at,
                'updated_at' => $dispatch->updated_at,
            ]);
        });
    }
}
