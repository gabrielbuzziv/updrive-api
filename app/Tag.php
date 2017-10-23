<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{

    /**
     * Database connection.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * Attributes that can be asign.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Tag belongs to many contacts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contacts()
    {
        return $this->belongsTo(User::class, 'contact_tag', 'contact_id', 'tag_id');
    }
}
