<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\SurveyRequest;
use App\Models\Survey;
use App\Models\User;
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
        $survey->user_id = $user_id; // company_id
        $survey->project_id = $request->project_id;
        $survey->survey_name = $request->survey_name;
        $survey->emoji_or_star = $request->emoji_or_star;
        $survey->repeat_status = $request->repeat_status; //once, daily,weekly,monthly
        $survey->start_date = $request->start_date;
        $survey->end_date = $request->end_date;
        $survey->save();
        $message = 'Request accepted successfully';
        $image = auth()->user()->image;
        $name = auth()->user()->name;
        $time = $survey->created_at;
        $user = User::where('id', $user_id)->first();
        $result = app('App\Http\Controllers\NotificationController')->sendNotification($image, $name, $time, $message,$user,true);
        return response()->json([
            'message' => 'Survey created successfully',
            'data' => $survey,
            'notification' => $result,
        ], 200);
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
    public function destroy( string $id)
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

}
