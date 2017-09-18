<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactAddress extends Model
{

    /**
     * The attribute set the database connection as account.
     *
     * @var string
     */
    protected $connection = 'account';
    
    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id', 'postcode', 'street', 'number', 'complement',
        'district', 'city', 'state',
    ];

    /**
     * The attribute set the table name.
     *
     * @var string
     */
    protected $table = 'contacts_address';

    /**
     * Turn timestamp off.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * A contact address belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }
}
