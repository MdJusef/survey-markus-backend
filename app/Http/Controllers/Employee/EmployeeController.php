<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyWiseProjectRequest;
use App\Http\Requests\JoinCompanyRequest;
use App\Http\Requests\ProjectBasedSurveyRequest;
use App\Models\Answer;
use App\Models\AssignProject;
use App\Models\CompanyJoin;
use App\Models\Project;
use App\Models\Question;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function showCompany(Request $request)
    {
        $query = User::where('role_type', 'COMPANY');
        $join_company = CompanyJoin::where('user_id', auth()->user()->id)->whereIn('status',['accepted','pending'])->get();

        if ($request->filled('name')) {
            $query->where('name', 'like', $request->name . '%');
        }

        $companies = $query->paginate(20);

        $companies->getCollection()->transform(function ($company) use ($join_company) {
            $joinCompanyStatus = $join_company->firstWhere('company_id', $company->id);

            $company->status = $joinCompanyStatus ? $joinCompanyStatus->status : 'default';

            return $company;
        });
        return response()->json( $companies);
    }


    public function joinCompany(JoinCompanyRequest $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $company_id = $request->company_id;
        $company = User::where('role_type', 'COMPANY')->where('id', $company_id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $exist_request = CompanyJoin::where('user_id', $user_id)->where('company_id', $company->id)->first();
        if ($exist_request) {
            return response()->json(['message' => 'You already send request'], 208);
        }
        $company_join = new CompanyJoin();
        $company_join->company_id = $company_id;
        $company_join->user_id = $user_id;
        $company_join->status = 'pending';
        $company_join->save();
        $image = auth()->user()->image;
        $name = auth()->user()->name;
        $message = 'Successfully sent a request to join the company.';
        $time = $company_join->created_at;
        $user = User::where('id', $company_id)->first();
        $result = app('App\Http\Controllers\NotificationController')->sendCompanyNotification($image, $name, $message, $time,$user,false);
        return response()->json([
            'message' => 'Successfully sent a request to join the company.',
            'data' => $company_join,
            'notification' => $result,
            ]);
    }

    public function companyWiseProjects(CompanyWiseProjectRequest $request)
    {
        $company_id = $request->company_id;
        $user_id = auth()->user()->id;

        $assignProjects = AssignProject::with('company')
            ->where('company_id', $company_id)
            ->where('user_id', $user_id)
            ->first();
        if ($assignProjects) {
            $query = Project::whereIn('id', $assignProjects->project_ids);

            if ($request->filled('project_name')) {
                $query->where('project_name', 'like', '%' . $request->project_name . '%');
            }

            $assignProjects->projects = $query->paginate(20);
            return response()->json(['data' => $assignProjects],200);
        }
        return response()->json(['message' => 'Projects not found'], 404);

    }

//    public function projectBasedSurvey(ProjectBasedSurveyRequest $request)
//    {
//        $project_id = $request->project_id;
//
//        $query = Survey::with('user')->where('project_id', $project_id);
//        if ($request->filled('survey_name')){
//            $query->where('survey_name', 'like' , '%' . $request->input('survey_name') . '%');
//        }
//        if ($request->filled('auth_user')){
//            $employee_id = auth()->user()->id;
//            $get_survey_ids = Answer::where('user_id', $employee_id)->pluck('survey_id')->unique();
//
//            // Modify the query to include only the surveys that exist in the Answer table
//            $query->whereIn('id', $get_survey_ids);
//        }
//        $surveys = $query->paginate(20);
//        return response()->json($surveys);
//    }

    public function projectBasedSurvey(ProjectBasedSurveyRequest $request)
    {
        $project_id = $request->project_id;


        $query = Survey::with('user')->where('project_id', $project_id);

        $today = now()->format('Y-m-d');
        $query->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);


        if ($request->filled('survey_name')) {
            $query->where('survey_name', 'like', '%' . $request->input('survey_name') . '%');
        }

        // Check if the authenticated user has attended the survey
//        if ($request->filled('auth_user')) {
//            $employee_id = auth()->user()->id;
//
//            // Get all survey ids that the user has answered
//            $answered_survey_ids = Answer::where('user_id', $employee_id)->pluck('survey_id')->unique();
//
//            // Modify the query to include only the surveys the user has answered
//            $query->whereIn('id', $answered_survey_ids);
//        }

        // Paginate the result
        $surveys = $query->paginate($request->per_page ?? 10);

        // Loop through each survey to check user's answer status and assign color
        foreach ($surveys as $survey) {
            $employee_id = auth()->user()->id;

            // Get total number of questions for this survey
            $total_questions = Question::where('survey_id', $survey->id)->count();

            // Get the number of answered questions for this survey by the user
            $answered_questions = Answer::where('survey_id', $survey->id)
                ->where('user_id', $employee_id)
                ->count();

            // Determine the color based on answer status
            if ($answered_questions == $total_questions) {
                $survey->color = 'gray'; // All questions answered
            } elseif ($answered_questions > 0) {
                $survey->color = 'green'; // Some questions answered
            } else {
                $survey->color = 'yellow'; // No questions answered
            }
        }

        return response()->json($surveys);
    }


    public function showJoinCompany()
    {
        $auth_user_id = auth()->user()->id;
        if (!$auth_user_id){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $company = CompanyJoin::where('user_id',$auth_user_id)->with('user')->where('status','accepted')->paginate(20);
        return response()->json( $company,200);
    }
}
