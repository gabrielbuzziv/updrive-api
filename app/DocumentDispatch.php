<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentDispatch extends Model
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
    protected $table = 'documents_dispatch';

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'user_id', 'subject', 'message'];

    /**
     * A document dispatch has one dispatcher.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A document dispatch belongs to a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * A document dispatch has many documents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'dispatch_id');
    }

    /**
     * A document dispatch has one or many contacts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts()
    {
        return $this->belongsToMany(User::class, 'document_dispatch_contact', 'dispatch_id', 'contact_id');
    }

    /**
     * Has many DocumentDispatchTracking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tracking()
    {
        return $this->hasMany(DocumentDispatchTracking::class, 'dispatch_id');
    }
}
