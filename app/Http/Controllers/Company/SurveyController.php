<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Answer;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\SurveyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;
use App\Models\AssignProject;
use App\Models\Question;

class SurveyController extends Controller
{

    public function index(Request $request)
    {
        // return $request->is_show;
        $company_id = auth()->user()->id;
        $query = Survey::where('user_id', $company_id);
        // ->whereDate('end_date','>=',now()->toDateString());
        if ($request->is_show == false) {
            $query->whereDate('end_date', '>=', now()->toDateString());
        }
        if ($request->filled('search')) {
            $query->where('survey_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $projects = $query->paginate($request->per_page ?? 10);
        return response()->json(['data' => $projects], 200);
    }

    public function store(SurveyRequest $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)) {
            return response()->json(['message' => 'unauthorized'], 401);
        }



        $survey = new Survey();
        $survey->user_id = $user_id; // company_id
        $survey->project_id = $request->project_id;
        $survey->survey_name = $request->survey_name;
        $survey->emoji_or_star = $request->emoji_or_star;
        $survey->repeat_status = $request->repeat_status; //once, daily,weekly,monthly
        $survey->start_date = $request->start_date;
        $survey->end_date = $request->end_date;
        $survey->save();

        $project_name = DB::table('projects')->where('id', $request->project_id)->value('project_name');

        $assignedUsers = AssignProject::whereJsonContains('project_ids', (int)$request->project_id)
                        ->get();

        $companyUser = User::find($user_id);

        $assignedUsers->each(function ($assignedUser) use ($survey, $project_name, $companyUser) {
            $user = User::find($assignedUser->user_id);
            if($user){
                $user->notify(new SurveyNotification([
                    'image' => $companyUser->image,
                    'name' => $companyUser->name,
                    'message' => "A new survey '{$survey->survey_name}' has been created under project '{$project_name}' by " . $companyUser->name,
                    'time' => $survey->created_at,
                    'data' => $companyUser,
                ]));
            }
        });

        // $user = User::find($user_id);
        // // $message = 'A new survey has been created by ' . $user->name;
        // $message = "A new survey '{$survey->survey_name}' has been created under project '{$project_name}' by " . $user->name;
        // $time = $survey->created_at;


        // $notificationController = new NotificationController();
        // $notify =  $notificationController->sendNotification($user->image, $user->name, $message, $time, $user, true);

        return response()->json([
            'message' => 'Survey created successfully',
            'data' => $survey,
        ], 200);
    }


    //    public function show(string $id)
    //    {
    //        $company_id = auth()->user()->id;
    //        $survey = Survey::with('project','questions','answers')->where('user_id',$company_id)->withCount('questions')->withCount('answers')->find($id);
    //        return response()->json($survey, 200);
    //    }
    //    public function show(string $id)
    //    {
    //        $company_id = auth()->user()->id;
    //
    //        // Fetch the survey with related data
    //        $survey = Survey::with('project','questions','answers')
    //            ->where('user_id', $company_id)
    //            ->withCount('questions')
    //            ->withCount('answers')
    //            ->find($id);
    //
    //        if (!$survey) {
    //            return response()->json(['error' => 'Survey not found'], 404);
    //        }
    //
    //        // Initialize counts for each answer option
    //        $answerCounts = [
    //            'count_1' => 0,
    //            'count_2' => 0,
    //            'count_3' => 0,
    //            'count_4' => 0,
    //            'count_5' => 0,
    //        ];
    //
    //        // Count the number of occurrences for each answer
    //        foreach ($survey->answers as $answer) {
    //            switch ($answer->answer) {
    //                case 1:
    //                    $answerCounts['count_1']++;
    //                    break;
    //                case 2:
    //                    $answerCounts['count_2']++;
    //                    break;
    //                case 3:
    //                    $answerCounts['count_3']++;
    //                    break;
    //                case 4:
    //                    $answerCounts['count_4']++;
    //                    break;
    //                case 5:
    //                    $answerCounts['count_5']++;
    //                    break;
    //            }
    //        }
    //
    //        // Add the answer counts to the survey object
    //        $survey->answer_counts = $answerCounts;
    //
    //        return response()->json($survey, 200);
    //    }

    // public function show(string $id)
    // {
    //     $company_id = auth()->user()->id;

    //     // Fetch the survey with related data and counts
    //     $survey = Survey::with(['project'])
    //         ->where('user_id', $company_id)
    //         ->withCount('questions')
    //         ->withCount('answers')
    //         ->find($id);

    //     if (!$survey) {
    //         return response()->json(['error' => 'Survey not found'], 404);
    //     }

    //     // Count the number of occurrences for each answer option directly in the query
    //     $answerCounts = $survey->answers()
    //         ->selectRaw('answer, COUNT(*) as count')
    //         ->groupBy('answer')
    //         ->pluck('count', 'answer')
    //         ->toArray();

    //     // Initialize the answer counts array with default values
    //     $counts = [
    //         'count_1' => $answerCounts[1] ?? 0,
    //         'count_2' => $answerCounts[2] ?? 0,
    //         'count_3' => $answerCounts[3] ?? 0,
    //         'count_4' => $answerCounts[4] ?? 0,
    //         'count_5' => $answerCounts[5] ?? 0,
    //     ];

    //     // Add the answer counts to the survey object
    //     $survey->answer_counts = $counts;

    //     return response()->json($survey, 200);
    // }
    public function show(Request $request, string $id)
    {
        $company_id = auth()->user()->id;

        // Fetch the survey with related data and counts
        $survey = Survey::with(['project'])
            ->where('user_id', $company_id)
            ->withCount('questions')
            ->withCount('answers')
            ->withCount('anonymous_survey_answers')
            ->find($id);

        collect([$survey])->transform(function ($survey) {
            $survey->answers_count = $survey->answers_count + $survey->anonymous_survey_answers_count;
            return $survey;
        });

        $survey->makeHidden(['anonymous_survey_answers_count']);

        if (!$survey) {
            return response()->json(['error' => 'Survey not found'], 404);
        }

        // Count the number of occurrences for each answer option directly in the query
        $answerCounts = $survey->answers()
            ->selectRaw('answer, COUNT(*) as count')
            ->groupBy('answer')
            ->union(
                DB::table('anonymous_survey_answers')
                    ->selectRaw('answer, COUNT(*) as count')
                    ->where('survey_id', $id)
                    ->groupBy('answer')
            )
            ->pluck('count', 'answer')
            ->toArray();
        // return $answerCounts[1] ?? 0;
        // Initialize the answer counts array with default values
        $counts = [
            'count_1' => $answerCounts[1] ?? 0,
            'count_2' => $answerCounts[2] ?? 0,
            'count_3' => $answerCounts[3] ?? 0,
            'count_4' => $answerCounts[4] ?? 0,
            'count_5' => $answerCounts[5] ?? 0,
        ];

        //     $monthlySurveyRatings = Answer::selectRaw('
        //     survey_id,
        //     DATE_FORMAT(created_at, "%Y-%m") as month,
        //     AVG(answer) as avg_rating
        // ')
        // ->groupBy('survey_id', 'month')
        // ->orderBy('month', 'ASC')
        // ->get();

        // $monthlyAverageRatings = Answer::selectRaw('
        //     survey_id,
        //     question_id,
        //     DATE_FORMAT(created_at, "%Y-%M") as month,
        //     AVG(answer) as avg_rating
        // ')
        // ->groupBy('survey_id', 'question_id', 'month')
        // ->union(
        //     DB::table('anonymous_survey_answers')
        //         ->selectRaw('
        //             survey_id,
        //             question_id,
        //             DATE_FORMAT(created_at, "%Y-%M") as month,
        //             AVG(answer) as avg_rating
        //         ')
        //         ->groupBy('survey_id', 'question_id', 'month')
        // )
        // ->orderBy('month', 'ASC')
        // ->get();
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $ratings = $survey->answers()
            ->selectRaw('survey_id, question_id, DATE_FORMAT(created_at, "%M") as month,COUNT(id) as count, SUM(answer) as total_rating')
            ->where('survey_id', $id)
            ->where('question_id', $request->question_id)
            ->groupBy('survey_id', 'question_id', 'month')
            ->union(
                DB::table('anonymous_survey_answers')
                    ->selectRaw('survey_id, question_id, DATE_FORMAT(created_at, "%M") as month, COUNT(id) as count, SUM(answer) as total_rating')
                    ->where('survey_id', $id)
                    ->where('question_id', $request->question_id)
                    ->groupBy('survey_id', 'question_id', 'month')
            )
            ->orderByRaw("FIELD(month, '" . implode("','", $months) . "')")
            ->get();
        // return $ratings;
        $monthlyAverageRatings = collect($months)->map(function ($month) use ($ratings) {
            return [
                'month' => $month,
                'avg_rating' => $ratings->where('month', $month)->sum('total_rating') / ($ratings->where('month', $month)->sum('count') ?: 1),
            ];
        });


        // Initialize the monthly ratings array with default values
        // $monthlySurveyRatings = [
        //     'month' => $monthlySurveyRatings->pluck('month'),
        //     'avg_rating' => $monthlySurveyRatings->pluck('avg_rating'),
        // ];

        // Add the monthly ratings to the survey object
        $survey->monthly_ratings = $monthlyAverageRatings;

        // Add the answer counts to the survey object
        $survey->answer_counts = $counts;

        return response()->json($survey, 200);
    }




    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)) {
            return response()->json(['message' => 'unauthorized'], 401);
        }
        $survey = Survey::find($id);
        if (empty($survey)) {
            return response()->json(['message' => 'Survey not found'], 404);
        }
        $survey->start_date = $request->start_date ?? $survey->start_date;
        $survey->end_date = $request->end_date ?? $survey->end_date;
        $survey->update();
        return response()->json([
            'message' => 'Survey update successfully',
            'data' => $survey,
        ], 200);
    }
    public function destroy(string $id)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)) {
            return response()->json(['message' => 'unauthorized'], 401);
        }

        $survey = Survey::where('id', $id)->where('user_id', $user_id)->first();
        if (!$survey) {
            return response()->json(['message' => 'Survey not found or you are not authorized to delete this survey'], 404);
        }

        $survey->delete();
        return response()->json(['message' => 'Survey deleted successfully'], 200);
    }

    public function surveyBasedUser(Request $request)
    {

        $survey_id = $request->survey_id;
        $query = Answer::with('user', 'survey.user')->where('survey_id', $survey_id);
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        $questions = $query->paginate($request->per_page ?? 10);
        return response()->json($questions);
    }

    // survey based user answer and anonymous user answer
    // public function surveyBasedUser(Request $request)
    // {
    //     $survey_id = $request->survey_id;
    //     $survey = Survey::with('user')->find($survey_id);

    //     $survey = collect([$survey])->map(function ($item) {
    //         return [
    //             'survey_id' => $item->id,
    //             'survey_name' => $item->survey_name,
    //             'project_name' => $item->project->project_name,
    //             'total_questions' => $item->questions->count(),
    //             'total_survey' => $item->answers->unique('user_id')->count() + $item->anonymous_survey_answers->count(),
    //             'total_user' => $item->answers->unique('user_id')->count(),

    //         ];
    //     })->first();

    //     return response()->json($survey, 200);

    // }


    public function deleteSurveyUser(Request $request)
    {
        $id = $request->id;
        $query = Answer::find($id);
        if (!$query) {
            return response()->json(['message' => 'Survey user not found'], 404);
        }
        $query->delete();
        return response()->json(['message' => 'Survey user deleted successfully'], 200);
    }

    public function testQuery(Request $request)
    {

        $answers = Answer::with('user')
            ->select('user_id', 'survey_id', \DB::raw('MAX(id) as id'), \DB::raw('MAX(created_at) as created_at'))
            ->where('survey_id', $request->survey_id)
            ->groupBy('user_id', 'survey_id')
            ->with('user')
            ->get();

        // Get the company details using survey_id
        $survey = Survey::with('user')
            ->where('id', $request->survey_id)
            ->first();

        // Add survey_company details to each survey_user object
        $answers->each(function ($answer) use ($survey) {
            $answer->survey_company = $survey->user;
        });

        return response()->json($answers, 200);
    }

    //survey based question report
    public function surveyBasedQuestions(Request $request)
    {
        $survey_id = $request->survey_id;
        $questions = Question::where('survey_id', $survey_id)->get();
        if ($questions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No question found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }
}
