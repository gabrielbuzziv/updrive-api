<?php

namespace App\UPCont\Transformer;

use App\ContactPhone;
use League\Fractal\TransformerAbstract;

class ContactPhonesTransformer extends TransformerAbstract
{

    /**
     * Companies default transformation.
     *
     * @param ContactPhone $phone
     * @return array
     */
    public function transform(ContactPhone $phone)
    {
        return [
            'number' => $phone->number,
            'type'   => $phone->type,
        ];
    }
}