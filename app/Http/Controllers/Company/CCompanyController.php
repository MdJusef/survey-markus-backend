<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Project;
use App\Models\Survey;
use Illuminate\Http\Request;

class CCompanyController extends Controller
{
    public function companyDashboard(Request $request)
    {
        $auth_user_id = auth()->user()->id;
        $year = $request->input('year', now()->year); // Default to the current year if no year is provided

        // Total projects and surveys count
        $total_project = Project::where('user_id', $auth_user_id)
            ->whereYear('created_at', $year)
            ->count();

        $total_survey = Survey::where('user_id', $auth_user_id)
            ->whereYear('created_at', $year)
            ->count();

        // Total responses count filtered by year
        $total_response = Answer::whereHas('survey', function($query) use ($auth_user_id, $year) {
            $query->where('user_id', $auth_user_id)
                ->whereYear('created_at', $year);
        })->count();

        // Month-wise projects count filtered by year
        $projects_by_month = Project::where('user_id', $auth_user_id)
            ->whereYear('created_at', $year)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Ensure each month has a value
        $projects_by_month = $this->fillMissingMonths($projects_by_month, 'count', $year);

        // Month-wise responses count filtered by year
        $responses_by_month = Answer::whereHas('survey', function($query) use ($auth_user_id, $year) {
            $query->where('user_id', $auth_user_id)
                ->whereYear('created_at', $year);
        })
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(DISTINCT user_id) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Ensure each month has a value
        $responses_by_month = $this->fillMissingMonths($responses_by_month, 'count', $year);

        return response()->json([
            'total_project' => $total_project,
            'total_survey' => $total_survey,
            'total_response' => $total_response,
            'projects_by_month' => $projects_by_month,
            'responses_by_month' => $responses_by_month,
        ]);
    }

    private function fillMissingMonths($data, $key, $year)
    {
        $filledData = [];
        $start = now()->setYear($year)->startOfYear();
        $end = now()->setYear($year)->endOfYear();

        while ($start->lte($end)) {
            $monthData = $data->firstWhere('month', $start->month);
            $filledData[] = [
                'year' => $start->year,
                'month' => $start->month,
                $key => $monthData[$key] ?? 0,
            ];
            $start->addMonth();
        }

        return collect($filledData);
    }

}
