<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'survey_name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'repeat_status' => 'required|in:once,daily,weekly,monthly',
            'emoji_or_star' => 'required|in:emoji,star',
        ];
    }
}
