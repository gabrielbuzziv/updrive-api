<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ContactRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('manage-contacts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE': {
                return [];
            }
            case 'POST': {
                return [
                    'name'  => 'min:3',
                    'email' => 'required|email|unique:account.users,email,NULL,id,deleted_at,NULL,is_contact,1'
                ];
            }
            case 'PUT':
            case 'PATCH': {
                return [
                    'name'  => 'min:3',
                    'email' => 'required|email|unique:users,email,' . $this->contact->id,
                ];
            }
            default:
                break;
        }
    }
}
