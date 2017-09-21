<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('manage-users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email.*' => 'required|email|unique:account.users,email,NULL,id,deleted_at,NULL,is_user,1'
        ];
    }

    /**
     * Set custom names to the attributes from request.
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [];

        foreach ($this->request->get('email') as $index => $email) {
            $number = $index + 1;
            $attributes["email.{$index}"] = "{$number}º e-mail";
        }

        return $attributes;
    }
}
