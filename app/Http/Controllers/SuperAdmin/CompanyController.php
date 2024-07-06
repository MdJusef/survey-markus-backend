<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {

    }

    public function store(CompanyRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->company_id = rand(100000, 999999);
        $user->password = Hash::make($request->password);
        $user->otp = Str::random(6);
        $user->role_type = 'COMPANY';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $user->image = saveImage($request, 'image');
        }
        $user->email_verified_at = new Carbon();
        $user->save();
        return response()->json([
            'message' => 'Company added successfully',
            'data' => $user
        ]);
    }

    public function show(string $id)
    {
        $company = User::where('role_type','COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return response()->json(['data' => $company]);
    }


    public function update(Request $request, string $id)
    {
        $company = User::where('role_type', 'COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        $company->name = $request->name ?? $company->name;
        $company->email = $request->email ?? $company->email;
        $company->address = $request->address ?? $company->address;
        $company->phone_number = $request->phone_number ?? $company->phone_number;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($company->image) {
                removeImage($company->image);
            }
            $company->image = saveImage($request, 'image');
        }
        $company->save();
        return response()->json(['message' => 'Company updated successfully' , 'data' => $company], 200);
    }

    public function destroy(string $id)
    {
        $company = User::where('role_type', 'COMPANY')->where('id', $id)->first();
        if (empty($company)) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        if ($company->image) {
            removeImage($company->image);
        }
        $company->delete();
    }
}
