<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function index(Request $request)
    {
        $query = User::whereIn('role_type',['ADMIN','SUPER ADMIN']);
        if ($request->filled('search')) {
           $query->where('name','like','%'. $request->search . '%');
        }
        $admins = $query->paginate($request->per_page ?? 10);
        return response()->json($admins);
    }

    public function create()
    {
        //
    }

    public function store(AdminRequest $request)
    {
        $admin = new User();
        $admin->name = $request->name ?? null;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->role_type = $request->role_type;
        $admin->email_verified_at = new Carbon(today());
        $admin->otp = 0;
        $admin->save();
        return response()->json(['message' => 'Administration Created Successfully', 'data' => $admin]);
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
        $admin = User::whereIn('role_type',['ADMIN','SUPER ADMIN'])->find($id);
        if (empty($admin)) {
            return response()->json(['message' => 'Admin Not Found'], 404);
        }
        $admin->name = $request->name ?? $admin->name;
        $admin->email = $request->email ?? $admin->email;
        $admin->password = Hash::make($request->password) ?? $admin->password;
        $admin->role_type = $request->role_type ?? $admin->role_type;
        $admin->update();
        return response()->json(['message' => 'Administration Updated Successfully', 'data' => $admin]);
    }

    public function destroy(string $id)
    {
        $admin = User::whereIn('role_type',['ADMIN','SUPER ADMIN'])->find($id);
        if (empty($admin)) {
            return response()->json(['message' => 'Admin Not Found'], 404);
        }
        $admin->forceDelete();
        return response()->json(['message' => 'Admin Deleted Successfully']);
    }
}
