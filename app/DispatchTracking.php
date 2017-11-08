<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DispatchTracking extends Model
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
    protected $table = 'dispatches_tracking';

    /**
     * The attributes that can be assigned.
     *
     * @var array
     */
    protected $fillable = ['dispatch_id', 'recipient_id', 'status'];

    /**
     * Belongs to a dispatch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    /**
     * Belongs to a Contact (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
