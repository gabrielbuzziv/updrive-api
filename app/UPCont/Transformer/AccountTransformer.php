<?php

namespace App\UPCont\Transformer;

use App\Account;
use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
{

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
}
