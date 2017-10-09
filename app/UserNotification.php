<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{

    /**
     * Database connection.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'users_notification';

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'notification'];

    /**
     * A User notification belongs to user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
