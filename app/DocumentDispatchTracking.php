<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentDispatchTracking extends Model
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
    protected $table = 'documents_dispatch_tracking';

    /**
     * The attributes that can be assigned.
     *
     * @var array
     */
    protected $fillable = ['dispatch_id', 'contact_id', 'status'];

    /**
     * Belongs to a DocumentDispatch.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispatch()
    {
        return $this->belongsTo(DocumentDispatch::class);
    }

    /**
     * Belongs to a Contact (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(User::class);
    }
}
