<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Operations\UserController;
use App\Http\Controllers\SuperAdmin\Auth\RegistrationRequestController;
use App\Http\Controllers\SuperAdmin\Auth\StaffAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Authentication — Registration
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    // Type 1: personal customer — active immediately + token
    Route::post('register/customer', [RegisterController::class, 'customer']);

    // Type 2: company / workshop — submit a request, pending super-admin approval
    Route::post('register/company', [RegisterController::class, 'company']);
    Route::post('register/workshop', [RegisterController::class, 'workshop']);

    // Login (public) & logout (authenticated)
    Route::post('login', LoginController::class);
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');

    // Password: forgot / reset via token link (public)
    Route::post('forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('reset-password', [PasswordResetController::class, 'reset']);

    // Password: forgot / reset via emailed OTP code (public)
    Route::post('password/otp/send', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('password/otp/reset', [PasswordResetController::class, 'resetWithOtp']);

    // Password: change while authenticated
    Route::post('change-password', [PasswordResetController::class, 'change'])->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| Authenticated user — profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('profile')
    ->group(function () {
        Route::get('/showProfile', [UserController::class, 'showProfile']);
        Route::post('/editProfile', [UserController::class, 'editProfile']);
    });

/*
|--------------------------------------------------------------------------
| Super Admin — Staff accounts & registration approvals
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('admin')
    ->group(function () {
        // Type 3: super admin creates employee / branch admin accounts
        Route::post('employees', [StaffAccountController::class, 'storeEmployee']);
        Route::post('admins', [StaffAccountController::class, 'storeAdmin']);

        // Review pending company / workshop registration requests
        Route::get('registration-requests/companies', [RegistrationRequestController::class, 'companies']);
        Route::get('registration-requests/workshops', [RegistrationRequestController::class, 'workshops']);

        Route::post('registration-requests/companies/{company}/approve', [RegistrationRequestController::class, 'approveCompany']);
        Route::post('registration-requests/companies/{company}/reject', [RegistrationRequestController::class, 'rejectCompany']);
        Route::post('registration-requests/workshops/{workshop}/approve', [RegistrationRequestController::class, 'approveWorkshop']);
        Route::post('registration-requests/workshops/{workshop}/reject', [RegistrationRequestController::class, 'rejectWorkshop']);
    });
