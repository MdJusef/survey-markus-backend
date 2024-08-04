<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\answerRequest;
use App\Http\Requests\QuestionBasedReportRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Survey;
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

//    public function eQuestionBasedReport(Request $request)
//    {
//        $question_id = $request->question_id;
//        $survey_id = $request->survey_id;
//        return Answer::with('question')->where('survey_id',$survey_id)->where('question_id',$question_id)->get();
//    }

    public function eQuestionBasedReport(QuestionBasedReportRequest $request)
    {
        $survey_id = $request->survey_id;
        $question_id = $request->question_id;

        // Fetch all answers for the given survey
        $answers = Answer::with('question')
            ->where('survey_id', $survey_id)
            ->where('question_id', $question_id)
            ->get();

        // Group answers by question_id
        $groupedAnswers = $answers->groupBy('question_id');

        $results = [];

        foreach ($groupedAnswers as $question_id => $answers) {
            $totalAnswers = $answers->count();

            // Initialize counts for each option (assuming options are 1 to 5)
            $optionCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

            foreach ($answers as $answer) {
                $optionCounts[$answer->answer]++;
            }

            // Calculate percentages
            $optionPercentages = [];
            foreach ($optionCounts as $option => $count) {
                $optionPercentages[$option] = ($count / $totalAnswers) * 100;
            }

            // Prepare result for this question
            $results[] = [
                'question' => $answers->first()->question,
                'option_percentages' => $optionPercentages
            ];
        }

        return response()->json($results);
    }


}
