<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|min:2|max:100',
            'phone_number' => 'nullable|string|min:2|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'company_id' => 'nullable',
            'email' => 'required|string|email|max:60|unique:users',
            'password' => 'required|string|min:6|confirmed',
            //'role_type' => ['required', Rule::in(['EMPLOYEE','COMPANY', 'ADMIN', 'SUPER ADMIN'])],
        ];
    }
}
