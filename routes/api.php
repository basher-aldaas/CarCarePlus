<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\Operations\CarController;
use App\Http\Controllers\Operations\UserController;

use App\Http\Controllers\SuperAdmin\Auth\RegistrationRequestController;
use App\Http\Controllers\SuperAdmin\Auth\StaffAccountController;

/*
|--------------------------------------------------------------------------
| Authenticated User
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/register/customer', [RegisterController::class, 'customer']);
    Route::post('/register/company', [RegisterController::class, 'company']);
    Route::post('/register/workshop', [RegisterController::class, 'workshop']);

    Route::post('/login', LoginController::class);

    Route::post('/logout', LogoutController::class)
        ->middleware('auth:sanctum');

    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);

    Route::post('/password/otp/send', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('/password/otp/reset', [PasswordResetController::class, 'resetWithOtp']);

});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('profile')
    ->group(function () {

        Route::get('/showProfile', [UserController::class, 'showProfile']);

        Route::post('/updateProfile', [UserController::class, 'updateProfile']);

    });

/*
|--------------------------------------------------------------------------
| Cars
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')
    ->prefix('cars')
    ->group(function () {

        Route::get('/', [CarController::class, 'index'])
            ->middleware('permission:show.cars');

        Route::post('/', [CarController::class, 'store'])
            ->middleware('permission:add.car');

        Route::put('/{id}', [CarController::class, 'update'])
            ->middleware('permission:edit.car');

        Route::delete('/{id}', [CarController::class, 'destroy'])
            ->middleware('permission:delete.car');

    });

/*
|--------------------------------------------------------------------------
| Super Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('admin')
    ->group(function () {

        Route::post('/employees', [StaffAccountController::class, 'storeEmployee']);

        Route::post('/admins', [StaffAccountController::class, 'storeAdmin']);

        Route::get('/registration-requests/companies',
            [RegistrationRequestController::class, 'companies']);

        Route::get('/registration-requests/workshops',
            [RegistrationRequestController::class, 'workshops']);

        Route::post('/registration-requests/companies/{company}/approve',
            [RegistrationRequestController::class, 'approveCompany']);

        Route::post('/registration-requests/companies/{company}/reject',
            [RegistrationRequestController::class, 'rejectCompany']);

        Route::post('/registration-requests/workshops/{workshop}/approve',
            [RegistrationRequestController::class, 'approveWorkshop']);

        Route::post('/registration-requests/workshops/{workshop}/reject',
            [RegistrationRequestController::class, 'rejectWorkshop']);

    });
