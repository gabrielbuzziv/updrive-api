<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\AccountTransformer;
use Illuminate\Http\Request;

class AccountController extends ApiController
{

    use Transformable;

    /**
     * AccountController constructor.
     */
    public function __construct()
    {
        $this->middleware('permission:manage-account');
    }

    /**
     * Get current account.
     *
     * @return mixed
     */
    public function show()
    {
        $account = config('account');

        return $this->respond($this->transformItem($account, new AccountTransformer()));
    }
}
