<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountSetting extends Model
{

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'account_settings';

    /**
     * Disable timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Settings belongs to Account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
