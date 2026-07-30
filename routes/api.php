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
use App\Http\Controllers\SuperAdmin\Operations\CarBrandController;
use App\Http\Controllers\SuperAdmin\Operations\CarTypeController;
use App\Http\Controllers\SuperAdmin\Operations\PackageController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceSubServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PointsConfigController;
use App\Http\Controllers\SuperAdmin\Operations\PricingRuleController;
use App\Http\Controllers\SuperAdmin\Operations\PricingRuleTypeController;
use App\Http\Controllers\SuperAdmin\Operations\SubServiceController;
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
Route:: prefix('profile')
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
        Route::post('/{customer_id?}', [CarController::class, 'store'])->middleware('can:add.car'); //super admin, admin, customer
        Route::get('/show/{id}', [CarController::class, 'show'])->middleware('can:show.car'); //for all
        Route::post('/update/{id}', [CarController::class, 'update'])->middleware('can:edit.car'); //super admin, admin, customer
        Route::get('/delete/{id}', [CarController::class, 'destroy'])->middleware('can:delete.car'); //super admin, customer
    });

/*
|--------------------------------------------------------------------------
| Super Admin — Staff accounts & registration approvals
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])
    ->prefix('admin')
    ->group(function () {
        // Type 3: super admin creates staff accounts (washer / mechanic / admin)
        Route::post('employees', [StaffAccountController::class, 'storeEmployee'])->middleware('can:add.staff_account');//super admin

        // Review pending company / workshop registration requests
        Route::get('registration-requests/companies', [RegistrationRequestController::class, 'companies'])->middleware('can:show.registration_requests');//super admin
        Route::get('registration-requests/workshops', [RegistrationRequestController::class, 'workshops'])->middleware('can:show.registration_requests');//super admin

        Route::post('registration-requests/companies/{company}/approve', [RegistrationRequestController::class, 'approveCompany'])->middleware('can:manage.registration_requests');//super admin
        Route::post('registration-requests/companies/{company}/reject', [RegistrationRequestController::class, 'rejectCompany'])->middleware('can:manage.registration_requests');//super admin
        Route::post('registration-requests/workshops/{workshop}/approve', [RegistrationRequestController::class, 'approveWorkshop'])->middleware('can:manage.registration_requests');//super admin
        Route::post('registration-requests/workshops/{workshop}/reject', [RegistrationRequestController::class, 'rejectWorkshop'])->middleware('can:manage.registration_requests');//super admin
    });


Route::middleware('auth:sanctum')->group(function () {

    //for all
    Route::get('/categories',[CategoryController::class,'index'])->middleware('can:show.categories');
    Route::get('/categories/{id}',[CategoryController::class,'show'])->middleware('can:show.categories');
    Route::get('/services',[ServiceController::class,'index'])->middleware('can:show.services');
    Route::get('/services/{id}',[ServiceController::class,'show'])->middleware('can:show.services');
    Route::get('/sub-services',[SubServiceController::class,'index'])->middleware('can:show.sub_services');
    Route::get('/sub-services/{id}',[SubServiceController::class,'show'])->middleware('can:show.sub_services');
    Route::get('/car-types',[CarTypeController::class,'index'])->middleware('can:show.car_types');
    Route::get('/car-types/{id}',[CarTypeController::class,'show'])->middleware('can:show.car_types');
    Route::get('/car-brands',[CarBrandController::class,'index'])->middleware('can:show.car_brands');
    Route::get('/car-brands/{id}',[CarBrandController::class,'show'])->middleware('can:show.car_brands');
    Route::get('/packages',[PackageController::class,'index'])->middleware('can:show.packages');
    Route::get('/packages/{id}',[PackageController::class,'show'])->middleware('can:show.packages');
    Route::get('/package-services',[PackageServiceController::class,'index'])->middleware('can:show.package_services');
    Route::get('/package-services/{id}',[PackageServiceController::class,'show'])->middleware('can:show.package_services');
    Route::get('/package-service-sub-services',[PackageServiceSubServiceController::class,'index'])->middleware('can:show.package_service_sub_services');
    Route::get('/package-service-sub-services/{id}',[PackageServiceSubServiceController::class,'show'])->middleware('can:show.package_service_sub_services');
    Route::get('/pricing-rule-types', [PricingRuleTypeController::class, 'index'])->middleware('can:show.pricing_rule_types');
    Route::get('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'show'])->middleware('can:show.pricing_rule_types');
    Route::get('/pricing-rules', [PricingRuleController::class, 'index'])->middleware('can:show.pricing_rules');
    Route::get('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'show'])->middleware('can:show.pricing_rules');





    /*
     (SA , A) : can enter customer id and show his points or show all users points
      customer can show his points
    */
    //for SA & A & CUSTOMER
    Route::get('points/show/{customer_id?}', [PointController::class, 'show'])->middleware('can:show.user_points');
    Route::get('points/transactions/{customer_id?}', [PointsTransactionController::class, 'index'])->middleware('can:show.points_transactions');
    Route::get('points/transactions/show/{transaction}', [PointsTransactionController::class, 'show'])->middleware('can:show.points_transactions');
    Route::get('user-packages/{customer_id?}', [UserPackageController::class, 'index'])->middleware('can:show.user_packages');
    Route::get('user-packages/show/{user_package}', [UserPackageController::class, 'show'])->middleware('can:show.user_packages');
    Route::post('/user-packages/{customer_id?}', [UserPackageController::class, 'store'])->middleware('can:add.user_package');
    Route::post('/user-packages/update/{user_package}', [UserPackageController::class, 'update'])->middleware('can:edit.user_package');


    //for SA & A
    Route::get('/points', [PointController::class, 'index'])->middleware('can:show.all_user_points');
    Route::get('/points-configs', [PointsConfigController::class, 'index'])->middleware('can:show.point_config');
    Route::get('/points-configs/{id}', [PointsConfigController::class, 'show'])->middleware('can:show.point_config');

    //for SA
        //Route::post('/points/transactions', [PointsTransactionController::class, 'store']);
        Route::post('/points-configs', [PointsConfigController::class, 'store'])->middleware('can:manage.point_config');
        Route::post('/points-configs/{points_config}', [PointsConfigController::class, 'update'])->middleware('can:manage.point_config');
        Route::delete('/points-configs/{points_config}', [PointsConfigController::class, 'destroy'])->middleware('can:manage.point_config');

        Route::delete('/user-packages/{user_package}', [UserPackageController::class, 'destroy'])->middleware('can:manage.user_packages');

        Route::post('/packages',[PackageController::class,'store'])->middleware('can:manage.packages');
        Route::post('/packages/{package}',[PackageController::class,'update'])->middleware('can:manage.packages');
        Route::delete('/packages/{package}',[PackageController::class,'destroy'])->middleware('can:manage.packages');

        Route::post('/package-services',[PackageServiceController::class,'store'])->middleware('can:manage.package_services');
        Route::post('/package-services/{package_service}',[PackageServiceController::class,'update'])->middleware('can:manage.package_services');
        Route::delete('/package-services/{package_service}',[PackageServiceController::class,'destroy'])->middleware('can:manage.package_services');

        Route::post('/package-service-sub-services',[PackageServiceSubServiceController::class,'store'])->middleware('can:manage.package_service_sub_services');
        Route::post('/package-service-sub-services/{package_service_sub_service}',[PackageServiceSubServiceController::class,'update'])->middleware('can:manage.package_service_sub_services');
        Route::delete('/package-service-sub-services/{package_service_sub_service}',[PackageServiceSubServiceController::class,'destroy'])->middleware('can:manage.package_service_sub_services');

        Route::post('/categories',[CategoryController::class,'store'])->middleware('can:manage.categories');
        Route::post('/categories/{category}',[CategoryController::class,'update'])->middleware('can:manage.categories');
        Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->middleware('can:manage.categories');

        Route::post('/services',[ServiceController::class,'store'])->middleware('can:manage.services');
        Route::post('/services/{service}',[ServiceController::class,'update'])->middleware('can:manage.services');
        Route::delete('/services/{service}',[ServiceController::class,'destroy'])->middleware('can:manage.services');

        Route::post('/sub-services',[SubServiceController::class,'store'])->middleware('can:manage.sub_services');
        Route::post('/sub-services/{sub_service}',[SubServiceController::class,'update'])->middleware('can:manage.sub_services');
        Route::delete('/sub-services/{sub_service}',[SubServiceController::class,'destroy'])->middleware('can:manage.sub_services');

        Route::post('/car-types',[CarTypeController::class,'store'])->middleware('can:manage.car_types');
        Route::post('/car-types/{car_type}',[CarTypeController::class,'update'])->middleware('can:manage.car_types');
        Route::delete('/car-types/{car_type}',[CarTypeController::class,'destroy'])->middleware('can:manage.car_types');

        Route::post('/car-brands',[CarBrandController::class,'store'])->middleware('can:manage.car_brands');
        Route::post('/car-brands/{car_brand}',[CarBrandController::class,'update'])->middleware('can:manage.car_brands');
        Route::delete('/car-brands/{car_brand}',[CarBrandController::class,'destroy'])->middleware('can:manage.car_brands');

        Route::post('/pricing-rule-types', [PricingRuleTypeController::class, 'store'])->middleware('can:add.pricing_rule_types');
        Route::post('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'update'])->middleware('can:edit.pricing_rule_types');
        Route::delete('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'destroy'])->middleware('can:delete.pricing_rule_types');

        Route::post('/pricing-rules', [PricingRuleController::class, 'store'])->middleware('can:add.pricing_rule');
        Route::post('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'update'])->middleware('can:edit.pricing_rule');
        Route::delete('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'destroy'])->middleware('can:delete.pricing_rule');

});
