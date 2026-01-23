<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class UpdateUserRequest extends FormRequest
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
        $rules = [
            'name'          => 'required',
            'email'         => 'required|email',
            'invoice_code'  => 'nullable|string|max:255',
            'role_id'       => 'required',
            'update_password' => 'boolean' // Add this rule
        ];
        
        // Conditionally add password rules if update_password is checked
        if ($this->input('update_password')) {
            $rules['password'] = 'required|confirmed|min:8';
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.required_if' => 'The password field is required when updating password.',
        ];
    }
}