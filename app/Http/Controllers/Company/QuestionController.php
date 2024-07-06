<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

    public function index(Request $request)
    {
//        return $projects = Project::with('surveys.questions')->paginate(10);
        $projects = Project::with(['surveys' => function ($query) {
            $query->withCount('questions');
        }])->paginate(10);

        return response()->json($projects);
    }

    public function create()
    {

    }

//    public function store(Request $request)
//    {
//        $user_id = auth()->user()->id;
//        if (empty($user_id)){
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//        $question = new Question();
//        $question->user_id = $user_id;
//        $question->project_id = $request->project_id;
//        $question->survey_id = $request->survey_id;
//        $question->question = $request->question;
//        $question->save();
//        return response()->json(['message' => 'Question added successfully', 'data' => $question]);
//    }

//    public function store(Request $request)
//    {
//        $user_id = auth()->user()->id;
//        if (empty($user_id)) {
//            return response()->json(['message' => 'Unauthorized'], 401);
//        }
//
//        $questions = $request->questions;
//        $responses = [];
//
//        foreach ($questions as $q) {
//            $question = new Question();
//            $question->user_id = $user_id;
//            $question->project_id = $request->project_id;
//            $question->survey_id = $request->survey_id;
//            $question->questions = $q['questions'];
//            $question->comment = $q['comment'];
//            $question->save();
//
//            $responses[] = $question;
//        }
//
//        return response()->json([
//            'message' => 'Questions added successfully',
//            'data' => $responses
//        ]);
//    }

    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Debugging: Log the request payload
        \Log::info('Request payload:', $request->all());

        // Validate the request
        $request->validate([
            'project_id' => 'required|integer',
            'survey_id' => 'required|integer',
            'questions' => 'required|array',
            'questions.*.questions' => 'required|string',
            'questions.*.comment' => 'required|boolean'
        ]);

        $questions = $request->input('questions');
        \Log::info('Questions array:', $questions);

        $responses = [];

        foreach ($questions as $q) {
            $question = new Question();
            $question->user_id = $user_id;
            $question->project_id = $request->project_id;
            $question->survey_id = $request->survey_id;
            $question->questions = $q['question'];
            $question->comment = $q['comment'];
            $question->save();

            $responses[] = $question;
        }

        return response()->json([
            'message' => 'Questions added successfully',
            'data' => $responses
        ]);
    }



    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
