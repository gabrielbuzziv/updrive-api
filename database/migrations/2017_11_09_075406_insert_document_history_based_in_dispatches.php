<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertDocumentHistoryBasedInDispatches extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dispatches = DB::table('dispatches')->get();
        $dispatches->each(function ($dispatch) {
            $trackings = DB::table('dispatches_tracking')->where('dispatch_id', $dispatch->id)->get();
            $documents = DB::table('dispatch_document')->where('dispatch_id', $dispatch->id)->get();

            $documents->each(function ($document) use ($trackings) {
                $doc = DB::table('documents')->where('id', $document->document_id)->first();
                DB::table('documents_history')
                    ->where('user_id', $doc->user_id)
                    ->where('document_id', $doc->id)
                    ->where('action', 2)
                    ->delete();

                $trackings->each(function ($tracking) use ($document) {
                    DB::table('documents_history')->insert([
                        'user_id'     => $tracking->recipient_id,
                        'document_id' => $document->document_id,
                        'action'      => $this->getStatusId($tracking->status),
                        'created_at'  => $tracking->created_at
                    ]);
                });
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $dispatches = DB::table('dispatches')->get();
        $dispatches->each(function ($dispatch) {
            $trackings = DB::table('dispatches_tracking')->where('dispatch_id', $dispatch->id)->get();
            $documents = DB::table('dispatch_document')->where('dispatch_id', $dispatch->id)->get();

            $documents->each(function ($document) use ($trackings) {
                $trackings->each(function ($tracking) use ($document) {
                    DB::table('documents_history')
                        ->where('user_id', $tracking->recipient_id)
                        ->where('document_id', $document->document_id)
                        ->where('action', $this->getStatusId($tracking->status))
                        ->where('created_at', $tracking->created_at)
                        ->where('action', '<>', 2)
                        ->delete();
                });
            });
        });
    }

    /**
     * Convert label status to id status.
     *
     * @param $status
     * @return int
     */
    private function getStatusId($status)
    {
        switch ($status) {
            case 'sent':
                return 2;
            case 'resent':
                return 6;
            case 'delivered':
                return 7;
            case 'opened':
            case 'read':
                return 8;
            case 'dropped':
            case 'bounced':
                return 9;
        }
    }
}
