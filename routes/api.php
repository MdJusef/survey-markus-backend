<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\QuestionController;
use App\Http\Controllers\Company\SurveyController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    ['middleware' => 'auth:api']
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/email-verified', [AuthController::class, 'emailVerified']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/profile', [AuthController::class, 'loggedUserData']);
    Route::post('/forget-pass', [AuthController::class, 'forgetPassword']);
    Route::post('/verified-checker', [AuthController::class, 'emailVerifiedForResetPass']);
    Route::post('/reset-pass', [AuthController::class, 'resetPassword']);
    Route::post('/update-pass', [AuthController::class, 'updatePassword']);
    Route::put('/profile/edit/{id}', [AuthController::class, 'editProfile']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
});


Route::middleware(['auth:api','admin'])->group(function () {
    Route::resource('/companies', CompanyController::class);
});

Route::middleware(['auth:api','company'])->group(function () {
    Route::resource('/projects', ProjectController::class)->except(['create', 'edit']);
    Route::resource('/surveys', SurveyController::class)->except(['create', 'edit']);
    Route::resource('/questions', QuestionController::class)->except(['create', 'edit']);
});


