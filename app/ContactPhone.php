<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactPhone extends Model
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
        'contact_id', 'number', 'type'
    ];

    /**
     * The attribute set the table name.
     *
     * @var string
     */
    protected $table = 'contacts_phones';

    /**
     * Turn timestamp off.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the database type value and convert to the stringed version.
     *
     * @param $value
     * @return string
     */
    public function getTypeAttribute($value)
    {
        switch ($value) {
            case 0:
                return [
                    'id' => $value,
                    'label' => 'Empresarial'
                ];
            case 1:
                return [
                    'id' => $value,
                    'label' => 'Residencial'
                ];
            case 2:
                return [
                    'id' => $value,
                    'label' => 'Celular'
                ];
            case 3:
                return [
                    'id' => $value,
                    'label' => 'Fax'
                ];
            case 4:
                return [
                    'id' => $value,
                    'label' => 'Outro'
                ];
        }
    }

    /**
     * A contact phone belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(User::class, 'contact_id');
    }
}
