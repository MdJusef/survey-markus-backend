<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllInOneRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'role_type' => 'In:COMPANY,EMPLOYEE',
            'per_page' => 'integer|min:1',

        ];
    }
}
