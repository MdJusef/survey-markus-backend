<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Project;
use Illuminate\Http\Request;

class ESurveyController extends Controller
{

    public function mySurvey()
    {
        $user_id = auth()->user()->id;

        // Fetch all answers for the authenticated user
        $answers = Answer::where('user_id', $user_id)->get();
        // Get unique project_ids from the related questions
        $project_ids = $answers->pluck('question.project_id')->unique();

        // Fetch the projects using the unique project_ids
        $projects = Project::whereIn('id', $project_ids)->paginate(20);

        return response()->json($projects);
    }
}
