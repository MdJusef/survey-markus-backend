<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ProjectAssignRequest;
use App\Mail\OtpMail;
use App\Models\AssignProject;
use App\Models\CompanyJoin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $per_page = $request->per_page ?? 10;

        $query = User::with('projects.surveys')->where('role_type', 'COMPANY');
        if ($request->filled('search'))
        {
            $search = $request->search;
             $query = $query->where('name','like', '%' . $search . '%');
        }
        $company_info = $query->paginate($per_page);
        $formattedData = $company_info->getCollection()->map(function($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'company_id' => $company->company_id,
                'image' => $company->image,
                'address' => $company->address,
                'phone_number' => $company->phone_number,
                'project_count' => $company->projects->count(),
                'survey_count' => $company->projects->sum(function($project) {
                    return $project->surveys->count();
                }),
                'tool_used' => $company->tool_used,
            ];
        });

        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
            $formattedData,
            $company_info->total(),
            $company_info->perPage(),
            $company_info->currentPage(),
            ['path' => $company_info->path()]
        );

        return response()->json($paginatedData, 200);
    }
    public function store(CompanyRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->company_id = rand(100000, 999999);
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number ?? null;
        $user->address = $request->address ?? null;
        $user->otp = Str::random(6);
        $user->role_type = 'COMPANY';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $user->image = saveImage($request, 'image');
        }
        $user->email_verified_at = new Carbon();
        $user->save();
        return response()->json([
            'message' => 'Company added successfully',
            'data' => $user
        ]);
    }

    public function show(string $id)
    {
        $company = User::where('role_type','COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return response()->json(['data' => $company]);
    }


    public function update(Request $request, string $id)
    {
        $company = User::where('role_type', 'COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $company->name = $request->name ?? $company->name;
        $company->email = $request->email ?? $company->email;
        $company->address = $request->address ?? $company->address;
        $company->phone_number = $request->phone_number ?? $company->phone_number;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($company->image) {
                removeImage($company->image);
            }
            $company->image = saveImage($request, 'image');
        }
        $company->save();
        return response()->json(['message' => 'Company updated successfully' , 'data' => $company], 200);
    }

    public function destroy(string $id)
    {
        $company = User::where('role_type', 'COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
//        if ($company->image) {
//            removeImage($company->image);
//        }
        $company->softdelete();
        return response()->json(['message' => 'Company deleted successfully'], 200);
    }

    public function showRequest(Request $request)
    {
        $company_id = auth()->user()->id;
        if (empty($company_id)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $employee_request = CompanyJoin::with('user_details')->where('company_id',$company_id)->whereIn('status',['pending','accepted'])->paginate($request->per_page ?? 10);
        return response()->json(['data' => $employee_request]);
    }

    public function acceptRequest(ProjectAssignRequest $request)
    {
        $company_id = auth()->user()->id;
        if (empty($company_id)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $id = $request->id;
        $user_id = $request->user_id;
        $project_ids = json_decode($request->project_ids);
        $company = CompanyJoin::with('user')->where('id', $id)->where('company_id',$company_id)->where('status','pending')->first();
        if (empty($company)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $projects = AssignProject::where('user_id', $user_id)->first();
        if (!array(empty($projects))) {
            return response()->json(['message' => 'Projects already assigned'], 401);
        }
        $assign_project = new AssignProject();
        $assign_project->user_id = $user_id;
        $assign_project->company_id = $company_id;
        $assign_project->project_ids = json_encode($project_ids);
        $assign_project->save();

        $company->status = 'accepted';
        $company->save();

        $message = 'Request accepted successfully';
        $image = auth()->user()->image;
        $name = auth()->user()->name;
        $time = $assign_project->created_at;
        $user = User::where('id', $user_id)->first();
        $result = app('App\Http\Controllers\NotificationController')->sendNotification($image, $name, $message, $time,$user,false);
        return response()->json([
            'message' => 'Projects assigned successfully',
            'notification' => $result,
        ], 200);
    }

    public function assignProjects(AssignProjectRequest $request)
    {
        $company_id = auth()->user()->id;
        $user_id = $request->user_id;
        $assigned_projects = AssignProject::where('user_id', $user_id)->where('company_id',$company_id)->first();
        if (empty($assigned_projects)) {
            return response()->json(['message' => 'Projects is not assigned'], 401);
        }
        $assigned_projects->project_ids = $request->project_ids ?? $assigned_projects->project_ids;
        $assigned_projects->update();
        return response()->json(['message' => 'Assigned projects update successfully'], 200);
    }

}
