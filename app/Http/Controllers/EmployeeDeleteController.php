<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeDeleteRequest;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeDeleteController extends Controller
{
    public function employeeDelete(EmployeeDeleteRequest $request)
    {
        $employee_id = auth()->user()->id;
        if (empty($employee_id)){
            return response()->json(['message'=>'Employee ID is empty'],404);
        }
        $current_password = $request->password;
        $user = User::where('id',$employee_id)->first();
        if ($user->status == 'deleted'){
            return response()->json(['message'=>'Employee delete request already send to admin'],208);
        }
        if (!password_verify($current_password, $user->password)) {
            return response()->json(['message'=>'Current password is incorrect'],404);
        }
        $user->status = 'deleted';
        $user->save();
        return response()->json(['message'=>'Your request to delete the account has been submitted to the admin'],200);

    }

    public function showDeleteEmployeeRequest(Request $request)
    {

        $query = User::where('status','deleted');
        if ($request->filled('name'))
        {
            $query->where('name','like','%' . $request->name . '%');
        }
        $delete_requests = $query->paginate(10);
        return response()->json($delete_requests,200);
    }

    public function employeeDeleteById($id)
    {
        $delete_user = User::where('id',$id)->first();
        if (empty($delete_user)){
            return response()->json(['message'=>'Employee ID is empty'],404);
        }
        $delete_user->delete();
        return response()->json(['message'=>'Employee Deleted Successfully'],200);
    }
}
