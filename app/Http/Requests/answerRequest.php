<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class answerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answer' => 'required',
            'user_id' => '',
            'question_id' => 'required',
            'comment' => 'nullable|min:3',
        ];
    }
}
