<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Driver;
use Illuminate\Support\Facades\Crypt;

class UpdateDriverRequest extends FormRequest
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
        $id = $this->route('driver');
        $rules = [
            'employeeid' => 'nullable|string|max:20|unique:drivers,employeeid,'.Crypt::decrypt($id),
            'password' => 'required|string|max:65535',
            'name' => 'required|string|max:255',
            'status' => 'required',
            'invoice_code' => 'required|string|max:10|unique:drivers,invoice_code,'.Crypt::decrypt($id),
            'remark' => 'nullable|string|max:255',
        ];

        return $rules;
    }
}
