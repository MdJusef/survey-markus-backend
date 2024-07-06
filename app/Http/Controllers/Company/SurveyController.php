<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{

    public function index(Request $request)
    {
        $query = Survey::query();

        if ($request->filled('search'))
        {
            $query->where('survey_name', 'like' , $request->search . '%');
        }

        $projects = $query->paginate(10);
        return response()->json(['data' => $projects], 200);
    }


    public function store(SurveyRequest $request)
    {
        $user_id = auth()->user()->id;
        if (empty($user_id)){
            return response()->json(['message' => 'unauthorized'], 401);
        }
        $survey = new Survey();
        $survey->user_id = $user_id;
        $survey->project_id = $request->project_id;
        $survey->survey_name = $request->survey_name;
        $survey->save();
        return response()->json(['message' => 'Survey created successfully', 'data' => $survey], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
