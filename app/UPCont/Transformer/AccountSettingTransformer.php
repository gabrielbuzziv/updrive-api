<?php

namespace App\UPCont\Transformer;

use App\Account;
use App\AccountSetting;
use League\Fractal\TransformerAbstract;

class AccountSettingTransformer extends TransformerAbstract
{

    /**
     * The attribute set the default fields to include.
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * The attribute set the available fields to include.
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @param AccountSetting $setting
     * @return array
     */
    public function transform(AccountSetting $setting)
    {
        return [
            'id'    => (int) $setting->id,
            'label' => $setting->label,
            'value' => $setting->value
        ];
    }

}
