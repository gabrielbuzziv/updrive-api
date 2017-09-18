<?php

namespace App\UPCont\Transformer;

use League\Fractal\TransformerAbstract;
use App\Permission;

class PermissionTransformer extends TransformerAbstract
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
     * Permission default transformation.
     *
     * @param Permission $permission
     * @return array
     */
    public function transform(Permission $permission)
    {
        return [
            'id'           => (int) $permission->id,
            'name'         => $permission->name,
            'display_name' => $permission->display_name,
            'description'  => $permission->description,
        ];
    }
}