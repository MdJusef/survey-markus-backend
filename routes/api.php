<?php

use App\Http\Controllers\APTController;
use App\Http\Controllers\ArchiveSurveyController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Company\CCompanyController;
use App\Http\Controllers\Company\EmployeeManageController;
use App\Http\Controllers\Company\ProjectController;
use App\Http\Controllers\Company\QuestionController;
use App\Http\Controllers\Company\SurveyController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Employee\EQuestionController;
use App\Http\Controllers\EmployeeDeleteController;
use App\Http\Controllers\ESurveyController;
use App\Http\Controllers\EventManageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\CompanyController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\DeleteEController;
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

    Route::get('/admin-dashboard', [DashboardController::class, 'adminDashboard']);

    Route::resource('/companies', CompanyController::class);

    Route::post('/apt', [AptController::class, 'store']);
    Route::post('/update-apt', [AptController::class, 'updateApt']);

    Route::get('/delete-employee-request', [EmployeeDeleteController::class, 'showDeleteEmployeeRequest']);
    Route::patch('/delete-employee/{id}', [EmployeeDeleteController::class, 'employeeDeleteById']);
    Route::get('/cancel-delete-employee/{id}', [EmployeeDeleteController::class, 'cancelDeleteEmployeeRequest']);

    Route::get('/admin-notifications', [NotificationController::class, 'adminNotification']);
//    Route::get('/read-notification/{id}', [NotificationController::class, 'readNotificationById']);
});

Route::middleware(['auth:api','company'])->group(function () {

    Route::get('/company-dashboard', [CCompanyController::class, 'companyDashboard']);

    Route::resource('/projects', ProjectController::class)->except(['create', 'edit']);
    Route::resource('/surveys', SurveyController::class)->except(['create', 'edit']);
    Route::resource('/questions', QuestionController::class)->except(['create', 'edit']);

    Route::post('/update-questions', [QuestionController::class, 'updateQuestions']);

    Route::get('/show-request', [CompanyController::class, 'showRequest']);
    Route::post('/accept-request', [CompanyController::class, 'acceptRequest']);
    Route::post('/assign-projects', [CompanyController::class, 'assignProjects']);

    Route::get('/show-assign-projects/{id}', [CompanyController::class, 'showAssignProjects']);

    Route::get('/question-based-report',[QuestionController::class, 'questionBasedReport']);
    Route::get('/question-based-user',[QuestionController::class, 'questionBasedUser']);


// Route to generate QR code for a specific survey using its unique code
    Route::post('/surveys/qrcode/{survey_id}', [EventManageController::class, 'generateQRCode']);

// Route to view questions for a specific survey using its unique code
    Route::get('/surveys-questions', [EventManageController::class, 'getSurveyQuestions']);


    Route::get('/company-notifications', [NotificationController::class, 'companyNotification']);

    Route::get('/archive-surveys', [ArchiveSurveyController::class, 'archiveSurveys']);

    Route::get('/survey-based-user', [SurveyController::class, 'surveyBasedUser']);
    Route::delete('/delete-survey-user', [SurveyController::class, 'deleteSurveyUser']);

    Route::get('/delete-event', [EventManageController::class, 'deleteEvent']);

    Route::get('/test-query', [SurveyController::class, 'testQuery']);

    Route::get('/show-joined-users',[EmployeeManageController::class, 'showJoinedUsers']);

    Route::put('/de-joined-users/{id}',[EmployeeManageController::class, 'deJoinedUsers']);

});

Route::get('/single-surveys-questions/{barcode}', [EventManageController::class, 'getSingleSurveyQuestions']);
Route::post('/anonymous-surveys', [EventManageController::class, 'anonymousSurveys']);
Route::get('/anonymous-survey-report', [EventManageController::class, 'anonymousSurveyReport']);

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

    Route::get('/notifications', [NotificationController::class, 'notifications']);

    Route::get('/mark-as-read', [NotificationController::class, 'userReadNotification']);

    Route::post('/delete-employee', [EmployeeDeleteController::class, 'employeeDelete']);

});

Route::get('/terms-condition', [AptController::class, 'termsCondition']);
Route::get('/privacy-policy', [AptController::class, 'privacyPolicy']);

Route::middleware(['auth:api','admin.company'])->group(function () {
    Route::get('/read-notification/{id}', [NotificationController::class, 'readNotificationById']);
});

Route::middleware(['auth:api','super.admin'])->group(function () {
    Route::resource('/admins', AdminController::class);
    Route::get('/show-trash-users', [DeleteEController::class, 'showTrashUsers']);
    Route::patch('/restore-trash-user/{id}', [DeleteEController::class, 'restoreTrashUsers']);
    Route::delete('/delete-user-permanently/{id}', [DeleteEController::class, 'permanentlyDeleteUsers']);

    Route::get('/manage-users', [DeleteEController::class, 'showUsers']);
});


Route::get('/test',[EventManageController::class, 'test']);
