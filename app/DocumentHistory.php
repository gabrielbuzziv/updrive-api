<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{

    /**
     * The attribute set the database connection as account.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * The attribute set the table name.
     *
     * @var string
     */
    protected $table = 'documents_history';

    /**
     *
     * The attributes that can be fillable.
     * @var array
     */
    protected $fillable = [
        'user_id', 'document_id', 'action'
    ];

    /**
     * The attributes that will be hide in the collection.
     *
     * @var array
     */
    protected $hidden = ['updated_at'];

    /**
     * Scope sent documents.
     *
     * @param $query
     * @return mixed
     */
    public function scopeSent($query)
    {
        return $query->where('action', 2);
    }

    /**
     * Scope opened documents.
     *
     * @param $query
     * @return mixed
     */
    public function scopeOpened($query)
    {
        return $query->whereIn('action', [3, 4]);
    }
    
    /**
     * A history belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A history belongs to a document.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
