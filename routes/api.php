<?php

use App\Http\Controllers\APTController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Company\CCompanyController;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\QuestionController;
use App\Http\Controllers\Company\SurveyController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\EQuestionController;
use App\Http\Controllers\ESurveyController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
});

Route::middleware(['auth:api','admin'])->group(function () {
    Route::resource('/companies', CompanyController::class);

    Route::post('/apt', [AptController::class, 'store']);
    Route::post('/update-apt', [AptController::class, 'updateApt']);
});

Route::middleware(['auth:api','company'])->group(function () {

    Route::get('/company-dashboard', [CCompanyController::class, 'companyDashboard']);

    Route::resource('/projects', ProjectController::class)->except(['create', 'edit']);
    Route::resource('/surveys', SurveyController::class)->except(['create', 'edit']);
    Route::resource('/questions', QuestionController::class)->except(['create', 'edit']);

    Route::get('/show-request', [CompanyController::class, 'showRequest']);
    Route::post('/accept-request', [CompanyController::class, 'acceptRequest']);

    Route::get('/question-based-report',[QuestionController::class, 'questionBasedReport']);
    Route::get('/question-based-user',[QuestionController::class, 'questionBasedUser']);
});

Route::middleware(['auth:api','employee'])->group(function (){
    //
    Route::get('/employee-question-based-report',[EQuestionController::class, 'eQuestionBasedReport']);

    Route::get('/show-company', [EmployeeController::class, 'showCompany']);
    Route::post('/join-company', [EmployeeController::class, 'joinCompany']);
    Route::get('/show-joined-company', [EmployeeController::class, 'showJoinCompany']);

    Route::get('/company-wise-projects', [EmployeeController::class, 'companyWiseProjects']);
    Route::get('/project-based-survey', [EmployeeController::class, 'projectBasedSurvey']);

    Route::get('/show-questions', [EQuestionController::class, 'showQuestions']);

    Route::post('/answer-question', [EQuestionController::class, 'answerQuestion']);

    Route::get('/show-answer-report',[EQuestionController::class, 'showAnswerReports']);

    Route::get('/my-survey', [ESurveyController::class, 'mySurvey']);

});


Route::get('/terms-condition', [AptController::class, 'termsCondition']);
Route::get('/privacy-policy', [AptController::class, 'privacyPolicy']);


