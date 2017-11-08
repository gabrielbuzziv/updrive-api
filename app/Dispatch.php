<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    /**
     * The attribute set the database connection as account.
     *
     * @var string
     */
    protected $connection = 'account';

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'dispatches';

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'sender_id', 'subject', 'message'];

    /**
     * A dispatch has one sender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A dispatch belongs to a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * A dispatch belongs to many documents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class);
    }

    /**
     * A dispatch belongs to many recipients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipients()
    {
        return $this->belongsToMany(User::class, 'dispatch_recipient', 'dispatch_id', 'recipient_id');
    }

    /**
     * A dispatch has many trackings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracking()
    {
        return $this->hasMany(DispatchTracking::class);
    }
}
