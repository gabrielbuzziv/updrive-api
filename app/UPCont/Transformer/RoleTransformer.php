<?php

namespace App\UPCont\Transformer;

use League\Fractal\TransformerAbstract;
use App\Role;

class RoleTransformer extends TransformerAbstract
{

    /**
     * The attribute set the default fields to include.
     *
     * @var array
     */
    protected $defaultIncludes = [

    ];

    /**
     * The attribute set the available fields to include.
     *
     * @var array
     */
    protected $availableIncludes = [
        'perms'
    ];

    /**
     * Role default transformation.
     *
     * @param Role $role
     * @return array
     */
    public function transform(Role $role)
    {
        return [
            'id'           => (int) $role->id,
            'name'         => $role->name,
            'display_name' => $role->display_name,
            'description'  => $role->description,
        ];
    }

    /**
     * Include permissions transformer.
     *
     * @param Role $role
     * @return \League\Fractal\Resource\Collection
     */
    public function includePerms(Role $role)
    {
        return $this->collection($role->perms, new PermissionTransformer());
    }
}