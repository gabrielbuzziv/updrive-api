<?php

namespace App\UPCont\Transformer;

use App\Company;
use League\Fractal\TransformerAbstract;

class CompanyTransformer extends TransformerAbstract
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
        'address', 'contacts',
    ];

    /**
     * Companies default transformation.
     *
     * @param Company $company
     * @return array
     */
    public function transform(Company $company)
    {
        return [
            'id'             => (int) $company->id,
            'name'           => $company->name,
            'nickname'       => $company->nickname,
            'taxvat'         => $company->taxvat,
            'docnumber'      => $company->docnumber,
            'docnumber_town' => $company->docnumber_town,
            'email'          => $company->email,
            'phone'          => $company->phone,
            'identifier'     => $company->identifier,
        ];
    }

    /**
     * Include address to the transform.
     *
     * @param Company $company
     * @return \League\Fractal\Resource\Item
     */
    public function includeAddress(Company $company)
    {
        return $this->item($company->address, new CompanyAddressTransformer());
    }

    /**
     * Include contacts to the transform.
     *
     * @param Company $company
     * @return \League\Fractal\Resource\Collection
     */
    public function includeContacts(Company $company)
    {
        return $this->collection($company->contacts, new ContactTransformer());
    }
}