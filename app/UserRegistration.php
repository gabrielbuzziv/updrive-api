<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRegistration extends Model
{

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'users_registration';

    /**
     * The database connection.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = ['email', 'token'];


}
