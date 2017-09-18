<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
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
        'company_id', 'postcode', 'street', 'number', 'complement',
        'district', 'city', 'state'
    ];

    /**
     * The attribute set the table name.
     *
     * @var string
     */
    protected $table = 'companies_address';
    
    /**
     * Set timestamp off.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * A company address belongs to a company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
