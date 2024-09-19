<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnonymousSurveyRequest;
use App\Models\AnonymousSurveyAnswer;
use App\Models\Answer;
use App\Models\ManageBarcode;
use App\Models\Question;
use App\Models\Survey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventManageController extends Controller
{
    // Generate QR Code for a survey

    public function generateQRCode($id)
    {

        // Find the survey by ID
        $survey = Survey::with('questions')->findOrFail($id);

        $exist_barcode = ManageBarcode::where('survey_id',$survey->id)->first();
        if ($exist_barcode) {
            return response()->json(['message'=>'Barcode already exist'],208);
        }

        // Generate a unique code
        $uniqueCode = Str::uuid()->toString();

        $surveyBarcode = new ManageBarcode;
        $surveyBarcode->survey_id = $survey->id;
        $surveyBarcode->barcode = $uniqueCode;
        $surveyBarcode->save();

        return response()->json([
            'message' => 'Barcode generated successfully',
            'barcode' => $surveyBarcode,
        ]);
    }

    // Get survey questions
    public function getSurveyQuestions(Request $request)
    {
        $auth_user = auth()->user()->id;
        $query = ManageBarcode::with('survey.questions')->whereHas('survey', function ($query) use ($auth_user) {
            $query->where('user_id', $auth_user);
        });
        if ($request->filled('search')) {
            $query->whereHas('survey', function ($query) use ($request) {
                $query->where('survey_name','like', '%' . $request->search . '%');
            });
        }
        $survey = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'survey' => $survey,
        ]);
    }

    public function getSingleSurveyQuestions($barcode)
    {
        $survey = ManageBarcode::where('barcode',$barcode)->with('survey.questions')->first();

        return response()->json([
            'survey' => $survey,
        ]);
    }

    public function anonymousSurveys(AnonymousSurveyRequest $request)
    {
//        $ipAddress = $request->getClientIp();
        $ipAddress = $request->unique_id;
        $question_id = $request->question_id;
        $question = Question::where('id', $question_id)->first();
        if (empty($question)) {
            return response()->json(['message' => 'Question Does Not Exist'], 404);
        }

        $survey_id = $question->survey_id;
        $survey = Survey::where('id', $survey_id)->first();

        // Check if the user has already answered based on repeat_status
        $existing_answer = AnonymousSurveyAnswer::where('ip_address', $ipAddress)
            ->where('survey_id', $survey_id)
            ->where('question_id', $question_id)
            ->latest()
            ->first();

        if ($existing_answer) {
            $now = Carbon::now();
            $last_answer_time = Carbon::parse($existing_answer->created_at);

            switch ($survey->repeat_status) {
                case 'once':
                    return response()->json(['message' => 'You have already participated in this survey.'], 409);
                case 'daily':
                    if ($last_answer_time->isSameDay($now)) {
                        return response()->json(['message' => 'You have already participated in this survey today.'], 409);
                    }
                    break;
                case 'weekly':
                    if ($last_answer_time->isSameWeek($now)) {
                        return response()->json(['message' => 'You have already participated in this survey this week.'], 409);
                    }
                    break;
                case 'monthly':
                    if ($last_answer_time->isSameMonth($now)) {
                        return response()->json(['message' => 'You have already participated in this survey this month.'], 409);
                    }
                    break;
                default:
                    // No restriction
                    break;
            }
        }

        // Store the answer
        $answer = new AnonymousSurveyAnswer();
        $answer->ip_address = $ipAddress;
        $answer->survey_id = $survey_id;
        $answer->question_id = $request->question_id;

        if ($question->comment == 1) {
            $answer->comment = $request->comment;
        }

        $answer->answer = $request->answer ?? null;
        $answer->save();

        return response()->json(['message' => 'Answer Saved Successfully', 'data' => $answer], 200);
    }

    public function anonymousSurveyReport(Request $request)
    {
//        $ipAddress = $request->getClientIp();
        $ipAddress = $request->unique_id;

        $survey_id = $request->survey_id;

        // Retrieve the survey and project details
        $survey = Survey::with('project')->find($survey_id);
        if (!$survey) {
            return response()->json(['message' => 'Survey not found'], 404);
        }
        $emoji_or_star = $survey->emoji_or_star;

        // Count the total number of questions for the survey
        $total_questions = Question::where('survey_id', $survey_id)->count();

        // Retrieve the latest answers with their associated questions
        $answers = AnonymousSurveyAnswer::with('question')
            ->where('survey_id', $survey_id)
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('question_id')
            ->values();

        // Build the response structure
        $company_name = $survey->user->name;
        $response = [
            'company_name' => $company_name,
            'project_name' => $survey->project->project_name,
            'survey_name' => $survey->survey_name,
            'total_questions' => $total_questions,
            'emoji_or_star' => $emoji_or_star,
            'answers' => $answers,
        ];

        return response()->json($response);
    }

    public function deleteEvent(Request $request)
    {
        $id = request('id');
        $event = ManageBarcode::find($id);
        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }
        $event->delete();
        return response()->json(['message' => 'Event Deleted Successfully'], 200);
    }

    public function test(Request $request)
    {
        dd($request);
    }
}
