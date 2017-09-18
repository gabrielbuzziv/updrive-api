<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Filterable;
use App\Http\Controllers\Traits\Sortable;
use App\Http\Controllers\Traits\Transformable;
use App\Http\Requests\PermissionRequest;
use App\Permission;
use App\UPCont\Transformer\PermissionTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends ApiController
{

    use Transformable;

    /**
     * Show all regular permissions based in the request.
     *
     * @return mixed
     */
    public function index()
    {
        $permissions = Permission::get();

        return $this->respond([
            'items' => $this->transformCollection($permissions, new PermissionTransformer()),
        ]);
    }
}
