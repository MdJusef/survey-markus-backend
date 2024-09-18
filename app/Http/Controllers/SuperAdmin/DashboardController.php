<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Project;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function adminDashboard(Request $request)
    {
        $auth_user_id = auth()->user()->id;
        $year = $request->input('year', now()->year); // Default to the current year if no year is provided

        // Total projects and surveys count
        $total_company = User::where('role_type', 'COMPANY')->count();
        $total_removed_company = User::where('role_type','COMPANY')->onlyTrashed()->count();

        // Month-wise projects count filtered by year
        $users_by_month = User::where('role_type', 'EMPLOYEE')
            ->whereYear('created_at', $year)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Ensure each month has a value
        $projects_by_month = $this->fillMissingMonths($users_by_month, 'count', $year);

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
            'total_company' => $total_company,
            'total_added_company' => $total_company,
            'total_removed_company' => $total_removed_company,
            'users_by_month' => $users_by_month,
            'company_growth_by_month' => $responses_by_month,
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
