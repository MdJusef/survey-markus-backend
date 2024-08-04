<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Project;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{

    public function index(Request $request)
    {
        //$projects = Project::with('surveys.questions')->paginate(10);
        $projects = Project::with(['surveys' => function ($query) {
            $query->withCount('questions');
        }])->paginate(10);

        return response()->json($projects);
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'user_id' => 'integer',
            'project_id' => 'required|integer',
            'survey_id' => 'required|integer',
            'questions' => 'required',
            'questions.*.question_en' => 'string',
            //'questions.*.question_jer' => 'string',
            'questions.*.comment' => 'boolean'
        ]);

        $questions = $request->input('questions');
        Log::info('Questions array: ' . json_encode($questions));
        $questions = json_decode($questions, true);
        try {
            $responses = [];
            foreach ($questions as $q) {
                $question = new Question();
                $question->user_id = auth()->user()->id;
                $question->project_id = $request->project_id;
                $question->survey_id = $request->survey_id;
                $question->question_en = $q['question_en'];
                //$question->question_jer = $q['question_jer'];
                $question->comment = $q['comment'];
                $question->save();

                $responses[] = $question;
            }

            return response()->json([
                'message' => 'Questions added successfully',
                'data' => $responses
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding questions: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while adding the questions',
            ], 500);
        }
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

    public function questionBasedReport(Request $request)
    {
        $surveys = Survey::with(['questions.answer', 'project'])->get();

        $report = [];

        foreach ($surveys as $survey) {
            foreach ($survey->questions as $question) {
                $totalUsers = $question->answer->groupBy('user_id')->count();
                $totalComments = $question->answer->where('comment', '!=', null)->count();
//                $totalSurveys = $question->answer->groupBy('survey_id')->count();

                $optionCounts = $question->answer->groupBy('answer')->map->count();
                $optionPercentages = $optionCounts->map(function ($count) use ($totalUsers) {
                    return  ($totalUsers > 0) ? ($count / $totalUsers) * 100 : 0;
                });

                $report[] = [
                    'project' => $survey->project->name,
                    'survey' => $survey->name,
                    'question_id' => $question->id,
//                    'total_surveys' => $totalSurveys,
                    'total_comments' => $totalComments,
                    'total_users' => $totalUsers,
                    'option_percentages' => $optionPercentages
                ];
            }
        }

        return response()->json($report);
    }


    public function questionBasedUser(Request $request)
    {
        return $user = Answer::with('answer')->get();
    }
}
