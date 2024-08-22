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
        $company_id = auth()->user()->id;
        $questions = Survey::where('user_id',$company_id)->with('project')
            ->withCount('questions')->withCount('answers')->paginate(10);
        return response()->json($questions);
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
        $company_id = auth()->user()->id;
        $questions = Survey::where('user_id',$company_id)->where('id',$id)->with('project','questions','answers')->paginate(10);
        return response()->json($questions);
    }

    public function edit(string $id)
    {
        //
    }

//    public function update(Request $request, string $id)
//    {
//        //
//    }
    public function update(Request $request, string $id)
    {

    }


    public function destroy(string $id)
    {
        //
    }

    public function questionBasedReport(Request $request)
    {
        $survey_id = $request->input('survey_id');
        $project_id = $request->input('project_id');
        $options = [1, 2, 3, 4, 5];
        $optionCounts = collect([
//            1 => $optionCounts->get(1, 0),
//            2 => $optionCounts->get(2, 0),
//            3 => $optionCounts->get(3, 0),
//            4 => $optionCounts->get(4, 0),
//            5 => $optionCounts->get(5, 0),
        ]);

        $surveys = Survey::with(['questions.answer', 'project'])->where('project_id',$project_id)->where('id',$survey_id)->get();

        $report = [];

        foreach ($surveys as $survey) {
            foreach ($survey->questions as $question) {
                $totalUsers = $question->answer->groupBy('user_id')->count();
                $totalComments = $question->answer->where('comment', '!=', null)->count();
//                $totalSurveys = $question->answer->groupBy('survey_id')->count();

                $optionCounts = $question->answer->groupBy('answer')->map->count();
//                $optionPercentages = $optionCounts->map(function ($count) use ($totalUsers) {
//                    return  ($totalUsers > 0) ? ($count / $totalUsers) * 100 : 0;
//                });
                $optionPercentages = collect($options)->mapWithKeys(function ($option) use ($optionCounts, $totalUsers) {
                    $count = $optionCounts->get($option, 0);
                    return [$option => ($totalUsers > 0) ? ($count / $totalUsers) * 100 : 0];
                });

                $report[] = [
                    'project' => $survey->project->project_name,
                    'survey' => $survey->survey_name,
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
        $question_id = $request->input('question_id');

        $user = Answer::where('question_id',$question_id)->with('user')->paginate(10);
        return response()->json($user);
    }
    public function updateQuestions(Request $request)
    {
        try {
            // Decode the JSON input
            $questions = json_decode($request->input('questions'), true);

            // Check for JSON errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON format'], 400);
            }

            // Ensure the decoded JSON is an array
            if (!is_array($questions)) {
                return response()->json(['error' => 'Questions should be an array'], 400);
            }

            $responses = [];

            // Iterate over each question and update accordingly
            foreach ($questions as $question) {
                // Validate that the question ID exists
                if (!isset($question['id'])) {
                    return response()->json(['error' => 'Question ID is required'], 400);
                }

                // Find the question or return a 404 error
                $question_data = Question::find($question['id']);
                if (!$question_data) {
                    return response()->json(['error' => 'Question not found for ID ' . $question['id']], 404);
                }

                // Update the question fields
                $question_data->question_en = $question['question_en'] ?? $question_data->question_en;
                $question_data->comment = $question['comment'] ?? $question_data->comment;

                // Save the updated question
                $question_data->save();

                // Add the updated question to the response array
                $responses[] = $question_data;
            }

            return response()->json([
                'message' => 'Questions updated successfully',
                'data' => $responses
            ], 200);

        } catch (\Exception $e) {
            // Catch any exceptions and return a generic error message
            return response()->json(['error' => 'An error occurred while updating questions: ' . $e->getMessage()], 500);
        }
    }



}
