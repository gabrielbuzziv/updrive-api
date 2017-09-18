<?php

namespace App;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{

    /**
     * The attributes that can me assign.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'display_name'
    ];

    /**
     * The attributes that will not be shown in the
     * collection.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'pivot'];
}
