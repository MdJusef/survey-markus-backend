<?php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyWiseProjectRequest;
use App\Http\Requests\JoinCompanyRequest;
use App\Http\Requests\ProjectBasedSurveyRequest;
use App\Models\AssignProject;
use App\Models\CompanyJoin;
use App\Models\Project;
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

        $companies = $query->paginate(3);

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
        return response()->json(['message' => 'Successfully sent a request to join the company.' , 'data' => $company_join]);
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

            $assignProjects->projects = $query->paginate(3);
            return response()->json(['data' => $assignProjects],200);
        }
        return response()->json(['message' => 'Projects not found'], 404);

    }

    public function projectBasedSurvey(ProjectBasedSurveyRequest $request)
    {
        $project_id = $request->project_id;

        $query = Survey::with('user')->where('project_id', $project_id);
        if ($request->filled('survey_name')){
            $query->where('survey_name', 'like' , '%' . $request->input('survey_name') . '%');
        }
        $surveys = $query->paginate(3);
        return response()->json($surveys);
    }

    public function showJoinCompany()
    {
        $auth_user_id = auth()->user()->id;
        if (!$auth_user_id){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $company = CompanyJoin::with('user')->paginate(3);
        return response()->json( $company,200);
    }
}
