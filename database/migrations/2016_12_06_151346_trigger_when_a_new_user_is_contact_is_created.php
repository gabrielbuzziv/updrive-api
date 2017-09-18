<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TriggerWhenANewUserIsContactIsCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER tr_Contact_Address AFTER INSERT ON users FOR EACH ROW
            BEGIN
                IF (NEW.is_contact = 1) THEN
                    INSERT INTO contacts_address(contact_id) VALUES(NEW.id);
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER tr_Contact_Address");
    }
}
