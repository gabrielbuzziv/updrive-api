<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\Transformable;
use App\UPCont\Transformer\UserTransformer;
use App\User;
use App\UserRegistration;
use Illuminate\Http\Request;

class UserRegistrationController extends ApiController
{

    use Transformable;

    public function isValid()
    {
        if (request('email') && request('token')) {
            $valid = (new UserRegistration)
                ->where('email', request('email'))
                ->where('token', request('token'))
                ->first();

            if ($valid) {
                $user = User::where('email', $valid->email)->first();

                return $this->transformItem($user, new UserTransformer());
            }

            return $this->respondNotFound(null);
        }

        return $this->respondNotFound(null);
    }
}
