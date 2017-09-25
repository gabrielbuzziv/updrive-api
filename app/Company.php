<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait;

class Company extends Model
{
    use SearchableTrait, SoftDeletes;

    /**
     * The attribute set the database connection as account.
     * 
     * @var string
     */
    protected $connection = 'account';
    
    /**
     * Serchable filter.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'companies.name' => 9,
            'companies.identifier' => 10
        ]
    ];

    /**
     * The attributes that can be assign.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nickname', 'taxvat', 'docnumber', 'docnumber_town',
        'email', 'phone', 'identifier',
    ];

    /**
     * The attribtues that is not shown in collection.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Set the nickname value equals the name of the company
     * if the nickname is empty.
     *
     * @param $value
     */
    public function setNicknameAttribute($value)
    {
        $this->attributes['nickname'] = empty($value) ? $this->name : $value;
    }

    /**
     * Set the taxvat attribute.
     *
     * @param $value
     */
    public function setTaxvatAttribute($value)
    {
        $this->attributes['taxvat'] = empty($value) ? null : $value;
    }

    /**
     * Set the docnumber attribute.
     *
     * @param $value
     */
    public function setDocnumberAttribute($value)
    {
        $this->attributes['docnumber'] = empty($value) ? null : $value;
    }

    /**
     * Set the docnumber_town attribute.
     *
     * @param $value
     */
    public function setDocnumberTownAttribute($value)
    {
        $this->attributes['docnumber_town'] = empty($value) ? null : $value;
    }

    /**
     * Set the email attribute.
     *
     * @param $value
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = empty($value) ? null : $value;
    }

    /**
     * Set the phone attribute.
     *
     * @param $value
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = empty($value) ? null : $value;
    }

    /**
     * Set the identifier attribute.
     *
     * @param $value
     */
    public function setIdentifierAttribute($value)
    {
        $this->attributes['identifier'] = empty($value) ? null : $value;
    }

    /**
     * A company belongs to many users that is_contact.
     */
    public function contacts()
    {
        return $this->belongsToMany(User::class, 'company_contact', 'company_id', 'contact_id')
                    ->with('address', 'phones');
    }

    /**
     * A company have one company address.
     *
     * @return mixed
     */
    public function address()
    {
        return $this->hasOne(CompanyAddress::class);
    }

    /**
     * A company has many documents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
