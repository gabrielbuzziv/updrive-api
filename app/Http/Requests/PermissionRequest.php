<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PermissionRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('manage-permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'         => 'required|min:3',
            'display_name' => 'required|min:3',
            'description'  => 'min:6',
        ];
    }

    /**
     * Set a custom name to the validated attributes.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name'         => 'permissão',
            'display_name' => 'nome',
            'description'  => 'descrição',
        ];
    }
}
