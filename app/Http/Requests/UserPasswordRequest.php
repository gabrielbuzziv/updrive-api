<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password'              => 'required|min:6|confirmed:password_confirmation',
            'password_confirmation' => 'required',
        ];
    }

    /**
     * Lang attributes from request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'password'              => 'senha',
            'password_confirmation' => 'confirmação de senha',
        ];
    }
}
