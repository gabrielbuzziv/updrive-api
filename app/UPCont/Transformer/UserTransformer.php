<?php

namespace App\UPCont\Transformer;

use League\Fractal\TransformerAbstract;
use App\User;

class UserTransformer extends TransformerAbstract
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
        'notifications', 'permissions', 'email'
    ];

    /**
     * User default transformation.
     *
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        $gravatarUrl = 'https://www.gravatar.com/avatar/';

        return [
            'id'     => (int) $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'photo'  => $user->photo,
            'active' => (bool) $user->is_active,
            'contact' => (bool) $user->is_contact,
            'links' => [
                'gravatar' => sprintf('%s%s', $gravatarUrl, md5(strtolower($user->email)))
            ]
        ];
    }

    /**
     * Include Permission transformer.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection
     */
    public function includePermissions(User $user)
    {
        return $this->collection($user->permissions, function ($permission) {
            return $permission;
        });
    }

    /**
     * Include notifications transformer.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection
     */
    public function includeNotifications(User $user)
    {
        return $this->collection($user->unreadNotifications, new NotificationTransformer());
    }
}