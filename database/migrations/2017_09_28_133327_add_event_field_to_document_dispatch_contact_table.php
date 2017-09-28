<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventFieldToDocumentDispatchContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_dispatch_contact', function (Blueprint $table) {
            $table->string('status')->default('queue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_dispatch_contact', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
