<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Operations\CarController;
use App\Http\Controllers\Operations\CategoryController;
use App\Http\Controllers\Operations\ServiceController;
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
    Route::post('login', LoginController::class);// for all
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');// for all

    // Password: forgot / reset via token link (public)
    Route::post('forgot-password', [PasswordResetController::class, 'forgot']);// for all
    Route::post('reset-password', [PasswordResetController::class, 'reset']);// for all

    // Password: forgot / reset via emailed OTP code (public)
    Route::post('password/otp/send', [PasswordResetController::class, 'sendResetOtp']);// for all
    Route::post('password/otp/reset', [PasswordResetController::class, 'resetWithOtp']);// for all

});

/*
|--------------------------------------------------------------------------
| Authenticated user — profile
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('profile')
    ->group(function () {
        Route::get('/showProfile', [UserController::class, 'showProfile']); // for all
        Route::post('/updateProfile', [UserController::class, 'updateProfile']); // for all
    });

/*
|--------------------------------------------------------------------------
| Authenticated user — cars
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('cars')
    ->group(function () {
        //get all cars in system for super admin and all car in branch for admin
        Route::get('/all', [CarController::class, 'indexDashboard'])->middleware('can:show.cars');//super admin, admin
        //for super admin and admin we send customer id to show all his cars
        //for customer we don't send customer id
        Route::get('/indexClient/{customer_id?}', [CarController::class, 'indexClient'])->middleware('can:show.client.cars');//super admin, admin, customer
        //for super admin and admin we send customer id to add car for him
        //for customer we don't send customer id
        Route::post('/{customer_id?}', [CarController::class, 'store'])->middleware('can:add.car')->middleware('can:add.car'); //super admin, admin, customer
        Route::get('/show/{id}', [CarController::class, 'show'])->middleware('can:show.car'); //for all
        Route::post('/update/{id}', [CarController::class, 'update'])->middleware('can:edit.car'); //super admin, admin, customer
        Route::get('/delete/{id}', [CarController::class, 'destroy'])->middleware('can:delete.car'); //super admin, customer
    });

/*
|--------------------------------------------------------------------------
| Super Admin — Staff accounts & registration approvals
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('admin')
    ->group(function () {
        // Type 3: super admin creates staff accounts (washer / mechanic / admin)
        Route::post('employees', [StaffAccountController::class, 'storeEmployee']);//super admin

        // Review pending company / workshop registration requests
        Route::get('registration-requests/companies', [RegistrationRequestController::class, 'companies']);//super admin
        Route::get('registration-requests/workshops', [RegistrationRequestController::class, 'workshops']);//super admin

        Route::post('registration-requests/companies/{company}/approve', [RegistrationRequestController::class, 'approveCompany']);//super admin
        Route::post('registration-requests/companies/{company}/reject', [RegistrationRequestController::class, 'rejectCompany']);//super admin
        Route::post('registration-requests/workshops/{workshop}/approve', [RegistrationRequestController::class, 'approveWorkshop']);//super admin
        Route::post('registration-requests/workshops/{workshop}/reject', [RegistrationRequestController::class, 'rejectWorkshop']);//super admin
    });


Route::middleware('auth:sanctum')->group(function () {

    //for all
    Route::get('/categories',[CategoryController::class,'index']);
    Route::get('/categories/{id}',[CategoryController::class,'show']);
    Route::get('/services',[ServiceController::class,'index']);
    Route::get('/services/{id}',[ServiceController::class,'show']);


    //for SA
    Route::middleware('role:super_admin')->group(function () {

        Route::post('/categories',[CategoryController::class,'store']);
        Route::post('/categories/{category}',[CategoryController::class,'update']);
        Route::delete('/categories/{category}',[CategoryController::class,'destroy']);

        Route::post('/services',[ServiceController::class,'store']);
        Route::post('/services/{service}',[ServiceController::class,'update']);
        Route::delete('/services/{service}',[ServiceController::class,'destroy']);

    });

});
