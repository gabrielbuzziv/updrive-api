<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('manage-core');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:3',
            'nickname' => 'min:3',
            'email' => 'email',
        ];
    }

    /**
     * Custom validation attributes names.
     *
     * @return array
     */
    public function attributes()
    {
       return [
           'name' => 'razão social',
           'nickname' => 'nome fantasia',
           'email' => 'e-mail',
       ];
    }
}
