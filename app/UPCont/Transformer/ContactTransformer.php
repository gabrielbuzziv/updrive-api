<?php

namespace App\UPCont\Transformer;

use App\User;
use League\Fractal\TransformerAbstract;

class ContactTransformer extends TransformerAbstract
{

    /**
     * The attribute set the default includes of the transformer.
     *
     * @var array
     */
    protected $defaultIncludes = [

    ];

    /**
     * The attribute set the available includes of the transformer.
     *
     * @var array
     */
    protected $availableIncludes = [
        'address', 'phones', 'roles', 'companies'
    ];

    /**
     * Companies default transformation.
     *
     * @param User $contact
     * @return array
     */
    public function transform(User $contact)
    {
        $gravatarUrl = 'https://www.gravatar.com/avatar/';

        return [
            'id'     => (int) $contact->id,
            'name'   => $contact->name,
            'email'  => $contact->email,
            'contact' => (bool) $contact->is_contact,
            'active' => (bool) $contact->is_active,
            'links' => [
                'gravatar' => sprintf('%s%s', $gravatarUrl, md5(strtolower($contact->email)))
            ]
        ];
    }

    /**
     * Include address to the transform.
     *
     * @param User $contact
     * @return \League\Fractal\Resource\Item
     */
    public function includeRoles(User $contact)
    {
        return $this->collection($contact->roles, new RoleTransformer());
    }

    /**
     * Include address to the transform.
     *
     * @param User $contact
     * @return \League\Fractal\Resource\Item
     */
    public function includeAddress(User $contact)
    {
        if ($contact->address) {
            return $this->item($contact->address, new ContactAddressTransformer());
        }
    }

    /**
     * Include phones to the transform.
     *
     * @param User $contact
     * @return \League\Fractal\Resource\Collection
     */
    public function includePhones(User $contact)
    {
        if ($contact->phones) {
            return $this->collection($contact->phones, new ContactPhonesTransformer());
        }
    }

    /**
     * Include companies transformer.
     *
     * @param User $contact
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompanies(User $contact)
    {
        if ($contact->companies) {
            return $this->collection($contact->companies, new CompanyTransformer());
        }
    }
}