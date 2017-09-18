<?php

namespace App\UPCont\Transformer;

use App\ContactAddress;
use League\Fractal\TransformerAbstract;

class ContactAddressTransformer extends TransformerAbstract
{

    /**
     * Companies default transformation.
     *
     * @param ContactAddress $address
     * @return array
     */
    public function transform(ContactAddress $address)
    {
        return [
            'postcode'   => $address->postcode,
            'street'     => $address->street,
            'number'     => (int) $address->number,
            'complement' => $address->complement,
            'district'   => $address->district,
            'city'       => $address->city,
            'state'      => $address->state,
        ];
    }
}