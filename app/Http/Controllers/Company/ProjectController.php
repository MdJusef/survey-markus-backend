<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $company_id = auth()->user()->id;
        $query = Project::where('user_id',$company_id);

        if ($request->filled('search'))
        {
            $search = $request->get('search');
            $query->where('project_name', 'like' , '%' . $search . '%');
        }

        $per_page = $request->per_page ?? 10;

        $projects = $query->paginate($per_page);
        return response()->json(['data' => $projects], 200);
    }

    public function store(ProjectRequest $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)){
            return response()->json(['message' => 'You are not authorized to access this page.'], 401);
        }
        $project = new Project();
        $project->user_id = $user_id;
        $project->project_name = $request->project_name;
        $project->save();
        return response()->json(['message' => 'Project created successfully', 'data' => $project]);
    }

    public function show(string $id)
    {

    }

    public function edit(string $id)
    {

    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)) {
            return response()->json(['message' => 'You are not authorized to access this page.'], 401);
        }

        // Find the project by id
        $project = Project::where('user_id', $user_id)->find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found or you do not have permission to delete this project.'], 404);
        }

        // Delete the project
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

}
