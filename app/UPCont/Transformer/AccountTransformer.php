<?php

namespace App\UPCont\Transformer;

use App\Account;
use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
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
        'settings',
    ];

    /**
     * A Fractal transformer.
     *
     * @param Account $account
     * @return array
     */
    public function transform(Account $account)
    {
        return [
            'id'      => (int) $account->id,
            'name'    => $account->name,
            'email'   => $account->email,
            'slug'    => $account->slug,
            'phone'   => '',
            'address' => [
                'postcode'   => '',
                'street'     => '',
                'number'     => '',
                'complement' => '',
                'district'   => '',
                'city'       => '',
                'state'      => '',
            ],
        ];
    }

    /**
     * Include account settings to the transform.
     *
     * @param Account $account
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSettings(Account $account)
    {
        return $this->collection($account->settings, new AccountSettingTransformer());
    }
}
