<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyJoin;
use Illuminate\Http\Request;

class EmployeeManageController extends Controller
{
    public function showJoinedUsers(Request $request)
    {
        $company_id = auth()->user()->id;
        $query = CompanyJoin::with('user_details')->where('company_id', $company_id);
        if ($request->filled('search'))
        {
            $query->whereHas('user_details', function ($q) use ($request) {
                $q->where('name','like', '%' . $request->search . '%');
            });
        }

        $joined_users = $query->paginate($request->per_page ?? 10);
        return response()->json($joined_users);
    }

    public function deJoinedUsers(string $id)
    {
        $joined_users = CompanyJoin::find($id);
        $joined_users->status = 'default';
        $joined_users->save();
        return response()->json(['message' => 'User de joined successfully']);

    }
}
