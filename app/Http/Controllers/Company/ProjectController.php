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
        $query = Project::query();

        if ($request->filled('search'))
        {
            $query->where('project_name', 'like' , $request->search . '%');
        }

        $projects = $query->paginate(10);
        return response()->json(['data' => $projects], 200);
    }


    public function store(ProjectRequest $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)){
            return response()->json(['error' => 'You are not authorized to access this page.'], 401);
        }
        $project = new Project();
        $project->user_id = $user_id;
        $project->project_name = $request->project_name;
        $project->save();
        return response()->json(['message' => 'Project created successfully', 'data' => $project]);
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
}
