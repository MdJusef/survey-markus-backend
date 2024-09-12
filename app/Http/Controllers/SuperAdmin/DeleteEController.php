<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllInOneRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DeleteEController extends Controller
{
    public function showUsers(AllInOneRequest $request)
    {
        $query = User::query();
        if ($request->filled('role_type'))
        {
            $query->where('role_type',$request->role_type);
        }
        if ($request->filled('search'))
        {
            $query->where('name','like','%'. $request->search . '%');
        }

        $users = $query->paginate($request->per_page ?? 10);
        return response()->json($users);
    }
    public function showTrashUsers(Request $request)
    {
        $query = User::onlyTrashed();

        if ($request->filled('search'))
        {
            $query->where('name','like','%'. $request->search . '%');
        }
        if ($request->filled('role_type')){
            $query->where('role_type',$request->role_type);
        }

        $trashedUsers = $query->paginate($request->per_page ?? 10);

        return response()->json($trashedUsers);
    }

    public function restoreTrashUsers(Request $request , string $id)
    {
        $trashedUsers = User::onlyTrashed()->find($id);
        if (empty($trashedUsers)) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $trashedUsers->restore();
        return response()->json(['message' => 'Trashed User Restore Successfully' , 'data' => $trashedUsers]);
    }

    public function permanentlyDeleteUsers(Request $request , string $id)
    {
        $trashedUsers = User::onlyTrashed()->find($id);
        if (empty($trashedUsers)) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $trashedUsers->forceDelete();
        return response()->json(['message' => 'Permanently delete user successfully' , 'data' => $trashedUsers]);
    }
}
