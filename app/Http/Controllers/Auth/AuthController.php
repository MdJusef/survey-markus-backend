<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $user = User::where('email', $request->email)
            ->where('email_verified_at', null)
            ->first();
        if ($user){
            $random = Str::random(6);
            Mail::to($request->email)->send(new OtpMail($random));
            $user->otp = $random;
            $user->save();
            return response(['message' => 'Please check your email for validate your email.'], 200);
        }else{
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|min:2|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'company_id' => 'nullable',
                'email' => 'required|string|email|max:60|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role_type' => ['required', Rule::in(['EMPLOYEE','COMPANY', 'ADMIN', 'SUPER ADMIN'])],
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->company_id = rand(100000, 999999);
            $user->password = Hash::make($request->password);
            $user->otp = Str::random(6);
            $user->role_type = $request->role_type;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $user->image = saveImage($request, 'image');
            }
            $user->save();
            Mail::to($request->email)->send(new OtpMail($user->otp));
            return response()->json([
                'message' => 'Please check your email to valid your email',
            ]);

        }
    }

    public function emailVerified(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }
        if ($request->otp) {
            $user = User::where('otp', $request->otp)->first();
            if ($user != null) {
                $token = $this->guard()->login($user);
            }
        }

        $user = User::where('otp', $request->otp)->first();

        if (!$user) {
            return response(['message' => 'Invalid'], 422);
        }
        $user->email_verified_at = new Carbon();
        $user->otp = 0;
        $user->save();

        return response([
            'message' => 'Email verified successfully',
            'token' => $this->respondWithToken($token),
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $userData = User::where('email', $request->email)->first();
        // return gettype($userData->otp);
//        if ($userData && Hash::check($request->password, $userData->password)) {
//            if ($userData->verify_email == 0) {
//                return response()->json(['message' => 'Your email is not verified'], 401);
//            }
//        }

        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['message' => 'Your credential is wrong'], 402);
    }

    public function guard()
    {
        return Auth::guard('api');
    }

    public function loggedUserData()
    {
        if ($this->guard()->user()) {
            $user = $this->guard()->user();

            return response()->json([
                'user' => $user
            ]);
        } else {
            return response()->json(['message' => 'You are unauthorized']);
        }
    }

    public function forgetPassword(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['error' => 'Email not found'], 401);
        } else if ($user->google_id != null || $user->apple_id != null) {
            return response()->json([
                'message' => 'Your are social user, You do not need to forget password',
            ], 400);
        } else {
            $random = Str::random(6);
            Mail::to($request->email)->send(new OtpMail($random));
            $user->update(['otp' => $random]);
            $user->update(['verify_email' => 0]);
            return response()->json(['message' => 'Please check your email for get the OTP']);
        }
    }

    public function emailVerifiedForResetPass(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return response()->json(['error' => 'Your verified code does not matched '], 401);
        } else {
            $user->update(['verify_email' => 1]);
            $user->update(['otp' => 0]);
            return response()->json(['message' => 'Now your email is verified'], 200);
        }
    }

    public function resetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Your email is not exists'
            ], 401);
        }
        if ($user->verify_email == 0) {
            return response()->json([
                'message' => 'Your email is not verified'
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(['message' => 'Password reset successfully'], 200);
        }
    }

    public function updatePassword(Request $request)
    {
        $user = $this->guard()->user();

        if ($user) {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'confirm_password' => 'required|string|same:new_password',
            ]);

            if ($validator->fails()) {
                return response(['errors' => $validator->errors()], 409);
            }
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Your current password is wrong'], 409);
            }
            $user->update(['password' => Hash::make($request->new_password)]);

            return response(['message' => 'Password updated successfully'], 200);
        } else {
            return response()->json(['message' => 'You are not authorized!'], 401);
        }
    }

    public function editProfile(Request $request, $id)
    {
        $user = $this->guard()->user();

        if ($user) {
            $validator = Validator::make($request->all(), [
                'fullName' => 'string|min:2|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $user->fullName = $request->fullName ? $request->fullName : $user->fullName;
            $user->mobile = $request->mobile ? $request->mobile : $user->mobile;
            $user->address = $request->address ? $request->address : $user->address;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destination = 'storage/image/' . $user->image;

                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $timeStamp = time();  // Current timestamp
                $fileName = $timeStamp . '.' . $file->getClientOriginalExtension();
                $file->storeAs('image', $fileName, 'public');

                $filePath = '/storage/image/' . $fileName;
                $fileUrl = $filePath;
                $user->image = $fileUrl;
            }

            $user->update();
            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'message' => 'You are not authorized!'
            ], 401);
        }
    }

    public function resendOtp(Request $request)
    {
        $user = User::where('email', $request->email)
            //            ->where('verify_email', 0)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found or email already verified'], 404);
        }

        // Check if OTP resend is allowed (based on time expiration)
        $currentTime = now();
        $lastResentAt = $user->last_otp_sent_at;  // Assuming you have a column in your users table to track the last OTP sent time

        // Define your expiration time (e.g., 5 minutes)
        $expirationTime = 5;  // in minutes

        if ($lastResentAt && $lastResentAt->addMinutes($expirationTime)->isFuture()) {
            // Resend not allowed yet
            return response()->json(['message' => 'You can only resend OTP once every ' . $expirationTime . ' minutes'], 400);
        }

        // Generate new OTP
        $newOtp = Str::random(6);
        Mail::to($user->email)->send(new OtpMail($newOtp));

        // Update user data
        $user->update(['otp' => $newOtp]);
        $user->update(['last_otp_sent_at' => $currentTime]);

        return response()->json(['message' => 'OTP resent successfully']);
    }


    protected function respondWithToken($token)
    {
        $user = Auth::guard('api')->user()->makeHidden(['contact_no', 'address','user_status', 'verify_email', 'otp', 'created_at', 'updated_at']);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'expires_in' => auth('api')
                    ->factory()
                    ->getTTL() * 600000000000,  // hour*seconds
        ]);
    }
}
