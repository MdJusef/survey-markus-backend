<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\answerRequest;
use App\Http\Requests\QuestionBasedReportRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EQuestionController extends Controller
{
    public function showQuestions(Request $request)
    {
        $survey_id = $request->survey_id;
        $question = Question::with('user')->where('survey_id', $survey_id)->get();
        if ($question->isEmpty()) {
            return response()->json(['message' => 'Question Does Not Exist'], 404);
        }
        return response()->json(['data' => $question],200);
    }

    public function answerQuestion(answerRequest $request)
    {
        $question_id = $request->question_id;
        $question = Question::where('id',$question_id)->first();
        if (empty($question)) {
            return response()->json(['message' => 'Question Does Not Exist'], 404);
        }
        $survey_id = $question->survey_id;
        $user_id = auth()->user()->id;
        if (empty($user_id)){
            return response()->json(['message' => 'User Not Logged In'], 401);
        }
        $answer = Answer::where('user_id',$user_id)->where('question_id',$request->question_id)->first();
        if (!empty($answer)){
            return response()->json(['message' => 'Answer is already submitted'], 404);
        }
        // answer should be 1,2,3,4,5;
        $answer = new Answer();
        $answer->user_id = auth()->user()->id;
        $answer->survey_id = $survey_id;
        $answer->question_id = $request->question_id;
        if ($question->comment == 1){
            $answer->comment = $request->comment;
        }
        $answer->answer = $request->answer ?? null;
        $answer->save();
        return response()->json(['message' => 'Answer Saved Successfully','data' => $answer],200);
    }

    public function showAnswerReports(Request $request)
    {
        $user_id = auth()->user()->id;
        $survey_id = $request->survey_id;

        // Retrieve the survey and project details
        $survey = Survey::with('project')->find($survey_id);
        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], 404);
        }
        $emoji_or_star = $survey->emoji_or_star;

        // Count the total number of questions for the survey
        $total_questions = Question::where('survey_id', $survey_id)->count();

        // Retrieve the answers with their associated questions
        $answers = Answer::with('question')
            ->where('user_id', $user_id)
            ->where('survey_id', $survey_id)
            ->get();

        // Build the response structure
        $response = [
            'project_name' => $survey->project->project_name,
            'survey_name' => $survey->survey_name,
            'total_questions' => $total_questions,
            'emoji_or_star' => $emoji_or_star,
            'answers' => $answers,
        ];

        return response()->json($response);
    }
    public function eQuestionBasedReport(QuestionBasedReportRequest $request)
    {
        $survey_id = $request->survey_id;
        $question_id = $request->question_id;
        $date_range = $request->date_range; // Expecting 'today', 'weekly', 'monthly', or specific month like 'January', or null for all data

        // Determine the date range
        if ($date_range) {
            switch ($date_range) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'weekly':
                    $startDate = now()->startOfWeek();
                    $endDate = now()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                    break;
                default:
                    // Assuming specific month format like 'January'
                    try {
                        $month = Carbon::parse($date_range)->month;
                        $startDate = now()->startOfYear()->month($month)->startOfMonth();
                        $endDate = now()->startOfYear()->month($month)->endOfMonth();
                    } catch (\Exception $e) {
                        return response()->json(['error' => 'Invalid date range format'], 400);
                    }
                    break;
            }
        }

        // Fetch all answers for the given survey
        $allAnswers = Answer::with('question.user')
            ->where('survey_id', $survey_id)
            ->where('question_id', $question_id)
            ->get();

        // Filter answers based on date range (if provided)
        $filteredAnswers = $allAnswers;
        if ($date_range) {
            $filteredAnswers = $allAnswers->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Group all answers by question_id
        $groupedAnswers = $allAnswers->groupBy('question_id');
        // Group filtered answers by question_id
        $groupedFilteredAnswers = $filteredAnswers->groupBy('question_id');

        $results = [];

        foreach ($groupedAnswers as $question_id => $answers) {
            $totalFilteredAnswers = isset($groupedFilteredAnswers[$question_id]) ? $groupedFilteredAnswers[$question_id]->count() : 0;

            // Initialize counts for each option (assuming options are 1 to 5)
            $optionCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

            if (isset($groupedFilteredAnswers[$question_id])) {
                foreach ($groupedFilteredAnswers[$question_id] as $answer) {
                    $optionCounts[$answer->answer]++;
                }
            }

            // Calculate percentages
            $optionPercentages = [];
            foreach ($optionCounts as $option => $count) {
                $optionPercentages[$option] = $totalFilteredAnswers > 0 ? ($count / $totalFilteredAnswers) * 100 : 0;
            }

            // Prepare result for this question
            $results[] = [
                'question' => $answers->first()->question,
                'option_percentages' => $optionPercentages
            ];
        }

        return response()->json(['data' => $results]);
    }
}
