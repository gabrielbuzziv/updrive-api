<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{

    /**
     * Set database connection.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = ['report_id', 'user_id', 'filters'];


    /**
     * User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
