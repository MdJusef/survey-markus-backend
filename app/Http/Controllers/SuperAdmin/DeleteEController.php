<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DeleteEController extends Controller
{
    public function showTrashUsers(Request $request)
    {
        $query = User::onlyTrashed();

        if ($request->filled('search'))
        {
            $query->where('name','like','%'. $request->search . '%');
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
