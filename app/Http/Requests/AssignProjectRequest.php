<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignProjectRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'user_id' => 'required|integer|exists:users,id',
//            'project_ids.*' => 'required|exists:projects,id'
            'user_id' => 'required|integer|exists:users,id',
            'project_ids' => 'required|',
            'project_ids.*' => 'required|integer|exists:projects,id'
        ];
    }
}
