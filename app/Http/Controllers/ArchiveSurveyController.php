<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class ArchiveSurveyController extends Controller
{
    public function archiveSurveys(Request $request)
    {
        $company_id = auth()->user()->id;
        $query = Survey::where('user_id', $company_id)
            ->with('project')
            // ->where('archive_status', 'true')
            ->whereDate('end_date', '<', now()->toDateString())
            ->withCount('questions')
            ->withCount('answers');
        if ($request->filled('search')){
             $search = $request->search;
            $query->where('survey_name','like','%'. $search . '%');
        }
        $questions = $query->paginate($request->per_page ?? 10);
        return response()->json($questions);
    }
}
