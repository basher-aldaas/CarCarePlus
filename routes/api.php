<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Operations\CarController;
use App\Http\Controllers\Operations\CategoryController;
use App\Http\Controllers\Operations\PointController;
use App\Http\Controllers\Operations\PointsTransactionController;
use App\Http\Controllers\Operations\ServiceController;
use App\Http\Controllers\Operations\UserController;
use App\Http\Controllers\Operations\UserPackageController;
use App\Http\Controllers\SuperAdmin\Auth\RegistrationRequestController;
use App\Http\Controllers\SuperAdmin\Auth\StaffAccountController;
use App\Http\Controllers\SuperAdmin\Operations\AdminController;
use App\Http\Controllers\SuperAdmin\Operations\CarBrandController;
use App\Http\Controllers\SuperAdmin\Operations\CarTypeController;
use App\Http\Controllers\SuperAdmin\Operations\PackageController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceSubServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PointsConfigController;
use App\Http\Controllers\SuperAdmin\Operations\SubServiceController;
use App\Models\User;
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
    Route::post('register/customer', [RegisterController::class, 'customer']);
    Route::post('register/company', [RegisterController::class, 'company']);
    Route::post('register/workshop', [RegisterController::class, 'workshop']);

    Route::post('login', LoginController::class);
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');

    Route::post('forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('reset-password', [PasswordResetController::class, 'reset']);

    Route::post('password/otp/send', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('password/otp/reset', [PasswordResetController::class, 'resetWithOtp']);
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
        Route::post('/updateProfile', [UserController::class, 'updateProfile']);
    });

/*
|--------------------------------------------------------------------------
| Authenticated user — cars
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('cars')
    ->group(function () {
        // Super Admin + Admin
        Route::get('/all', [CarController::class, 'indexDashboard'])
            ->middleware('can:show.cars');

        // Super Admin + Admin + Customer
        Route::get('/indexClient/{customer_id?}', [CarController::class, 'indexClient'])
            ->middleware('can:show.client.cars');

        // Super Admin + Admin + Customer
        Route::post('/{customer_id?}', [CarController::class, 'store'])
            ->middleware('can:add.car');

        // All authorized users
        Route::get('/show/{id}', [CarController::class, 'show'])
            ->middleware('can:show.car');

        // Super Admin + Admin + Customer
        Route::post('/update/{id}', [CarController::class, 'update'])
            ->middleware('can:edit.car');

        // Super Admin + Customer
        Route::get('/delete/{id}', [CarController::class, 'destroy'])
            ->middleware('can:delete.car');
    });

/*
|--------------------------------------------------------------------------
| Super Admin — Staff accounts & registration approvals
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {

        // Super Admin
        Route::post('employees', [StaffAccountController::class, 'storeEmployee'])
            ->middleware('can:add.staff_account');

        // Super Admin
        Route::get('registration-requests/companies', [RegistrationRequestController::class, 'companies'])
            ->middleware('can:show.registration_requests');

        Route::get('registration-requests/workshops', [RegistrationRequestController::class, 'workshops'])
            ->middleware('can:show.registration_requests');

        // Super Admin
        Route::post('registration-requests/companies/{company}/approve', [RegistrationRequestController::class, 'approveCompany'])
            ->middleware('can:manage.registration_requests');

        Route::post('registration-requests/companies/{company}/reject', [RegistrationRequestController::class, 'rejectCompany'])
            ->middleware('can:manage.registration_requests');

        Route::post('registration-requests/workshops/{workshop}/approve', [RegistrationRequestController::class, 'approveWorkshop'])
            ->middleware('can:manage.registration_requests');

        Route::post('registration-requests/workshops/{workshop}/reject', [RegistrationRequestController::class, 'rejectWorkshop'])
            ->middleware('can:manage.registration_requests');
    });

/*
|--------------------------------------------------------------------------
| Admin Management — Super Admin only add admin
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
    ->prefix('admins')
    ->group(function () {

        Route::get('/', [AdminController::class, 'index'])
            ->middleware('can:show.admins');

        Route::get('/{admin}', [AdminController::class, 'show'])
            ->middleware('can:show.admins');

        Route::post('/', [AdminController::class, 'store'])
            ->middleware('can:add.admin');

        Route::post('/{admin}', [AdminController::class, 'update'])
            ->middleware('can:edit.admin');

        Route::delete('/{admin}', [AdminController::class, 'destroy'])
            ->middleware('can:delete.admin');
    });

/*
|--------------------------------------------------------------------------
| General Operations — Authenticated Users
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('can:show.categories');

    Route::get('/categories/{id}', [CategoryController::class, 'show'])
        ->middleware('can:show.categories');

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */
    Route::get('/services', [ServiceController::class, 'index'])
        ->middleware('can:show.services');

    Route::get('/services/{id}', [ServiceController::class, 'show'])
        ->middleware('can:show.services');

    /*
    |--------------------------------------------------------------------------
    | Sub Services
    |--------------------------------------------------------------------------
    */
    Route::get('/sub-services', [SubServiceController::class, 'index'])
        ->middleware('can:show.sub_services');

    Route::get('/sub-services/{id}', [SubServiceController::class, 'show'])
        ->middleware('can:show.sub_services');

    /*
    |--------------------------------------------------------------------------
    | Car Types
    |--------------------------------------------------------------------------
    */
    Route::get('/car-types', [CarTypeController::class, 'index'])
        ->middleware('can:show.car_types');

    Route::get('/car-types/{id}', [CarTypeController::class, 'show'])
        ->middleware('can:show.car_types');

    /*
    |--------------------------------------------------------------------------
    | Car Brands
    |--------------------------------------------------------------------------
    */
    Route::get('/car-brands', [CarBrandController::class, 'index'])
        ->middleware('can:show.car_brands');

    Route::get('/car-brands/{id}', [CarBrandController::class, 'show'])
        ->middleware('can:show.car_brands');

    /*
    |--------------------------------------------------------------------------
    | Packages
    |--------------------------------------------------------------------------
    */
    Route::get('/packages', [PackageController::class, 'index'])
        ->middleware('can:show.packages');

    Route::get('/packages/{id}', [PackageController::class, 'show'])
        ->middleware('can:show.packages');

    /*
    |--------------------------------------------------------------------------
    | Package Services
    |--------------------------------------------------------------------------
    */
    Route::get('/package-services', [PackageServiceController::class, 'index'])
        ->middleware('can:show.package_services');

    Route::get('/package-services/{id}', [PackageServiceController::class, 'show'])
        ->middleware('can:show.package_services');

    /*
    |--------------------------------------------------------------------------
    | Package Service Sub Services
    |--------------------------------------------------------------------------
    */
    Route::get('/package-service-sub-services', [PackageServiceSubServiceController::class, 'index'])
        ->middleware('can:show.package_service_sub_services');

    Route::get('/package-service-sub-services/{id}', [PackageServiceSubServiceController::class, 'show'])
        ->middleware('can:show.package_service_sub_services');

    /*
    |--------------------------------------------------------------------------
    | Points — Super Admin + Admin + Customer
    |--------------------------------------------------------------------------
    */
    Route::get('points/show/{customer_id?}', [PointController::class, 'show'])
        ->middleware('can:show.user_points');

    Route::get('points/transactions/{customer_id?}', [PointsTransactionController::class, 'index'])
        ->middleware('can:show.points_transactions');

    Route::get('points/transactions/show/{transaction}', [PointsTransactionController::class, 'show'])
        ->middleware('can:show.points_transactions');

    Route::get('user-packages/{customer_id?}', [UserPackageController::class, 'index'])
        ->middleware('can:show.user_packages');

    Route::get('user-packages/show/{user_package}', [UserPackageController::class, 'show'])
        ->middleware('can:show.user_packages');

    Route::post('/user-packages/{customer_id?}', [UserPackageController::class, 'store'])
        ->middleware('can:add.user_package');

    Route::post('/user-packages/update/{user_package}', [UserPackageController::class, 'update'])
        ->middleware('can:edit.user_package');

    /*
    |--------------------------------------------------------------------------
    | Points — Super Admin + Admin
    |--------------------------------------------------------------------------
    */
    Route::get('/points', [PointController::class, 'index'])
        ->middleware('can:show.all_user_points');

    Route::get('/points-configs', [PointsConfigController::class, 'index'])
        ->middleware('can:show.point_config');

    Route::get('/points-configs/{id}', [PointsConfigController::class, 'show'])
        ->middleware('can:show.point_config');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Point Configuration
    |--------------------------------------------------------------------------
    */
    Route::post('/points-configs', [PointsConfigController::class, 'store'])
        ->middleware('can:manage.point_config');

    Route::post('/points-configs/{points_config}', [PointsConfigController::class, 'update'])
        ->middleware('can:manage.point_config');

    Route::delete('/points-configs/{points_config}', [PointsConfigController::class, 'destroy'])
        ->middleware('can:manage.point_config');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — User Packages
    |--------------------------------------------------------------------------
    */
    Route::delete('/user-packages/{user_package}', [UserPackageController::class, 'destroy'])
        ->middleware('can:manage.user_packages');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Packages
    |--------------------------------------------------------------------------
    */
    Route::post('/packages', [PackageController::class, 'store'])
        ->middleware('can:manage.packages');

    Route::post('/packages/{package}', [PackageController::class, 'update'])
        ->middleware('can:manage.packages');

    Route::delete('/packages/{package}', [PackageController::class, 'destroy'])
        ->middleware('can:manage.packages');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Package Services
    |--------------------------------------------------------------------------
    */
    Route::post('/package-services', [PackageServiceController::class, 'store'])
        ->middleware('can:manage.package_services');

    Route::post('/package-services/{package_service}', [PackageServiceController::class, 'update'])
        ->middleware('can:manage.package_services');

    Route::delete('/package-services/{package_service}', [PackageServiceController::class, 'destroy'])
        ->middleware('can:manage.package_services');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Package Service Sub Services
    |--------------------------------------------------------------------------
    */
    Route::post('/package-service-sub-services', [PackageServiceSubServiceController::class, 'store'])
        ->middleware('can:manage.package_service_sub_services');

    Route::post('/package-service-sub-services/{package_service_sub_service}', [PackageServiceSubServiceController::class, 'update'])
        ->middleware('can:manage.package_service_sub_services');

    Route::delete('/package-service-sub-services/{package_service_sub_service}', [PackageServiceSubServiceController::class, 'destroy'])
        ->middleware('can:manage.package_service_sub_services');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Categories
    |--------------------------------------------------------------------------
    */
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('can:manage.categories');

    Route::post('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('can:manage.categories');

    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('can:manage.categories');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Services
    |--------------------------------------------------------------------------
    */
    Route::post('/services', [ServiceController::class, 'store'])
        ->middleware('can:manage.services');

    Route::post('/services/{service}', [ServiceController::class, 'update'])
        ->middleware('can:manage.services');

    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
        ->middleware('can:manage.services');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Sub Services
    |--------------------------------------------------------------------------
    */
    Route::post('/sub-services', [SubServiceController::class, 'store'])
        ->middleware('can:manage.sub_services');

    Route::post('/sub-services/{sub_service}', [SubServiceController::class, 'update'])
        ->middleware('can:manage.sub_services');

    Route::delete('/sub-services/{sub_service}', [SubServiceController::class, 'destroy'])
        ->middleware('can:manage.sub_services');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Car Types
    |--------------------------------------------------------------------------
    */
    Route::post('/car-types', [CarTypeController::class, 'store'])
        ->middleware('can:manage.car_types');

    Route::post('/car-types/{car_type}', [CarTypeController::class, 'update'])
        ->middleware('can:manage.car_types');

    Route::delete('/car-types/{car_type}', [CarTypeController::class, 'destroy'])
        ->middleware('can:manage.car_types');

    /*
    |--------------------------------------------------------------------------
    | Super Admin only — Car Brands
    |--------------------------------------------------------------------------
    */
    Route::post('/car-brands', [CarBrandController::class, 'store'])
        ->middleware('can:manage.car_brands');

    Route::post('/car-brands/{car_brand}', [CarBrandController::class, 'update'])
        ->middleware('can:manage.car_brands');

    Route::delete('/car-brands/{car_brand}', [CarBrandController::class, 'destroy'])
        ->middleware('can:manage.car_brands');
});

/*
|--------------------------------------------------------------------------
| Admin Route Model Binding
|--------------------------------------------------------------------------
| {admin} must refer to a User who actually has the "admin" role.
|--------------------------------------------------------------------------
*/
Route::bind('admin', function ($value) {
    return User::role('admin')->findOrFail($value);
});
