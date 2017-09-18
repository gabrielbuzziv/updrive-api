<?php

namespace App\UPCont\Transformer;

use App\CompanyAddress;
use League\Fractal\TransformerAbstract;

class CompanyAddressTransformer extends TransformerAbstract
{

    /**
     * Companies default transformation.
     *
     * @param CompanyAddress $address
     * @return array
     */
    public function transform(CompanyAddress $address)
    {
        return [
            'postcode' => $address->postcode,
            'street' => $address->street,
            'number' => (int) $address->number,
            'complement' => $address->complement,
            'district' => $address->district,
            'city' => $address->city,
            'state' => $address->state
        ];
    }
}