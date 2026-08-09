<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Operations\BookingController;
use App\Http\Controllers\Operations\CarController;
use App\Http\Controllers\Operations\CategoryController;
use App\Http\Controllers\Operations\CompanyController;
use App\Http\Controllers\Operations\PointController;
use App\Http\Controllers\Operations\PointsTransactionController;
use App\Http\Controllers\Operations\ServiceController;
use App\Http\Controllers\Operations\UserController;
use App\Http\Controllers\Operations\UserPackageController;
use App\Http\Controllers\Operations\WorkshopController;
use App\Http\Controllers\SuperAdmin\Operations\AiRuleController;
use App\Http\Controllers\SuperAdmin\Operations\BranchController;
use App\Http\Controllers\SuperAdmin\Operations\CarBrandController;
use App\Http\Controllers\SuperAdmin\Operations\CarTypeController;
use App\Http\Controllers\SuperAdmin\Operations\CustomerCompanyController;
use App\Http\Controllers\SuperAdmin\Operations\CustomerPersonalController;
use App\Http\Controllers\SuperAdmin\Operations\InventoryController;
use App\Http\Controllers\SuperAdmin\Operations\InventoryTransactionController;
use App\Http\Controllers\SuperAdmin\Operations\MaterialController;
use App\Http\Controllers\SuperAdmin\Operations\MaterialUnitController;
use App\Http\Controllers\SuperAdmin\Operations\PackageController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PackageServiceSubServiceController;
use App\Http\Controllers\SuperAdmin\Operations\PointsConfigController;
use App\Http\Controllers\SuperAdmin\Operations\PricingRuleController;
use App\Http\Controllers\SuperAdmin\Operations\PricingRuleTypeController;
use App\Http\Controllers\SuperAdmin\Operations\ProblemTypeController;
use App\Http\Controllers\SuperAdmin\Operations\SubServiceController;
use App\Http\Controllers\SuperAdmin\Operations\SuggestedProblemController;
use App\Http\Controllers\SuperAdmin\Operations\SystemSettingController;
use App\Http\Controllers\SuperAdmin\Auth\RegistrationRequestController;
use App\Http\Controllers\SuperAdmin\Auth\StaffAccountController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\Operations\AdminController;

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



Route::middleware(['auth:sanctum', 'active.user'])
    ->group(function () {

        Route::post('bookings/{id}', [BookingController::class, 'update'])->whereNumber('id')->middleware('can:edit.order'); //super admin, admin, workshop, employee
        Route::delete('bookings/{id}', [BookingController::class, 'destroy'])->whereNumber('id')->middleware('can:cancel.order'); //super admin, admin, customer
        Route::post('bookings/{id}/assign', [BookingController::class, 'assign'])->whereNumber('id')->middleware('can:assign.order'); //super admin, admin, workshop
        Route::post('bookings/{id}/start', [BookingController::class, 'start'])->whereNumber('id')->middleware('can:edit.order'); //super admin, admin, workshop, employee
        Route::post('bookings/{id}/complete', [BookingController::class, 'complete'])->whereNumber('id')->middleware('can:edit.order'); //super admin, admin, workshop, employee
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


Route::middleware(['auth:sanctum'])->group(function () {

    //for all
    Route::get('/categories',[CategoryController::class,'index'])->middleware('can:show.categories');
    Route::get('/categories/{id}',[CategoryController::class,'show'])->middleware('can:show.categories');
    Route::get('/services',[ServiceController::class,'index'])->middleware('can:show.services');
    Route::get('/services/{id}',[ServiceController::class,'show'])->middleware('can:show.services');
    Route::get('/categories/{category}/services',[ServiceController::class,'indexByCategory'])->middleware('can:show.services');
    Route::get('/sub-services',[SubServiceController::class,'index'])->middleware('can:show.sub_services');
    Route::get('/sub-services/{id}',[SubServiceController::class,'show'])->middleware('can:show.sub_services');
    Route::get('/services/{service}/sub-services',[SubServiceController::class,'indexByService'])->middleware('can:show.sub_services');
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
    Route::get('/suggested-problems', [SuggestedProblemController::class, 'index'])->middleware('can:show.suggested_problems');
    Route::get('/suggested-problems/{suggested_problem}', [SuggestedProblemController::class, 'show'])->middleware('can:show.suggested_problems');
    Route::get('/branches', [BranchController::class, 'index'])->middleware('can:show.branches');
    Route::get('/branches/{branch}', [BranchController::class, 'show'])->middleware('can:show.branches');
    Route::get('/materials', [MaterialController::class, 'index'])->middleware('can:show.materials');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->middleware('can:show.materials');

    Route:: prefix('profile')
        ->group(function () {
            Route::get('/showProfile', [UserController::class, 'showProfile']);
            Route::post('/updateProfile', [UserController::class, 'updateProfile']);
        });

    Route::get('/companies',[CompanyController::class,'index'])->middleware('can:show.companies');

    Route::middleware('active.user')->group(function (){
        // list is auto-scoped per role: super admin/workshop see all, admin sees their branch's,
        // employee sees bookings assigned to them, customer sees their own
        Route::get('bookings/', [BookingController::class, 'index'])->middleware('can:show.orders');
        Route::get('bookings/{id}', [BookingController::class, 'show'])->middleware('can:show.orders');

    });

    Route::get('/indexClient/{customer_id?}', [CarController::class, 'indexClient'])->middleware('can:show.client.cars');
    Route::get('/show/{id}', [CarController::class, 'show'])->middleware('can:show.car');





    //for SA & A & Employee
    Route::middleware('active.user')->group(function (){
        Route::get('/users/{user}', [UserController::class, 'show'])->middleware('can:show.profile');
        Route::get('/pricing-rules', [PricingRuleController::class, 'index'])->middleware('can:show.pricing_rules');
        Route::get('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'show'])->middleware('can:show.pricing_rules');

    });



    /*
     (SA , A) : can enter customer id and show his points or show all users points
      customer can show his points
    */
    //for SA & A & CUSTOMER
    Route::middleware('active.user')->group(function (){
        Route::get('points/show/{customer_id?}', [PointController::class, 'show'])->middleware('can:show.user_points');
        Route::get('points/transactions/{customer_id?}', [PointsTransactionController::class, 'index'])->middleware('can:show.points_transactions');
        Route::get('points/transactions/show/{transaction}', [PointsTransactionController::class, 'show'])->middleware('can:show.points_transactions');
        Route::get('user-packages/{customer_id?}', [UserPackageController::class, 'index'])->middleware(['can:show.user_packages', 'active.user']);
        Route::get('user-packages/show/{user_package}', [UserPackageController::class, 'show'])->middleware('can:show.user_packages');
        Route::post('/user-packages/{customer_id?}', [UserPackageController::class, 'store'])->middleware('can:add.user_package');
        Route::post('/user-packages/update/{user_package}', [UserPackageController::class, 'update'])->middleware('can:edit.user_package');

        Route::get('/companies/my',[CompanyController::class,'myCompany'])->middleware('can:show.companies');
        Route::get('/companies/{company}',[CompanyController::class,'show'])->middleware('can:show.companies');
        Route::post('/companies/{company}',[CompanyController::class,'update'])->middleware('can:edit.company');
        Route::delete('/companies/{company}',[CompanyController::class,'destroy'])->middleware('can:delete.company');

        Route::post('/{customer_id?}', [CarController::class, 'store'])->middleware(['can:add.car', 'active.user']); //super admin, admin, customer
        Route::post('/update/{id}', [CarController::class, 'update'])->middleware(['can:edit.car', 'active.user']); //super admin, admin, customer
        Route::get('/delete/{id}', [CarController::class, 'destroy'])->middleware(['can:delete.car', 'active.user']); //super admin, customer

    });


    //for SA & A
    Route::middleware('active.admin')->group(function () {
        Route::get('/points', [PointController::class, 'index'])->middleware('can:show.all_user_points');
        Route::get('/points-configs', [PointsConfigController::class, 'index'])->middleware('can:show.point_config');
        Route::get('/points-configs/{id}', [PointsConfigController::class, 'show'])->middleware('can:show.point_config');
        Route::get('/inventories', [InventoryController::class, 'index'])->middleware('can:show.inventory');
        Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->middleware('can:show.inventory');
        Route::post('/inventories', [InventoryController::class, 'store'])->middleware('can:manage.inventory');
        Route::post('/inventories/{inventory}', [InventoryController::class, 'update'])->middleware('can:manage.inventory');
        Route::delete('/inventories/{inventory}', [InventoryController::class, 'destroy'])->middleware('can:manage.inventory');
        Route::get('/inventory-transactions', [InventoryTransactionController::class, 'index'])->middleware('can:show.inventory_transactions');
        Route::get('/inventory-transactions/{inventory_transaction}', [InventoryTransactionController::class, 'show'])->middleware('can:show.inventory_transactions');
        Route::post('/inventory-transactions', [InventoryTransactionController::class, 'store'])->middleware('can:manage.inventory_transactions');
        Route::get('/customers/personal',[CustomerPersonalController::class, 'index'])->middleware('can:show.personal_customers');
        Route::get('/customers/personal/{customer}',[CustomerPersonalController::class, 'show'])->middleware('can:show.personal_customers');
        Route::get('/customers/company',[CustomerCompanyController::class, 'index'])->middleware('can:show.company_customers');
        Route::get('/customers/company/{customer}',[CustomerCompanyController::class, 'show'])->middleware('can:show.company_customers');

        Route::get('/workshops', [WorkshopController::class, 'index'])->middleware('can:show.workshops');
        Route::get('/workshops/my', [WorkshopController::class, 'myWorkshop'])->middleware('can:show.workshops');
        Route::get('/workshops/{workshop}', [WorkshopController::class, 'show'])->middleware('can:show.workshops');

        Route::get('/all', [CarController::class, 'indexDashboard'])->middleware('can:show.cars');


    });
    Route::get('/material-units', [MaterialUnitController::class, 'index'])->middleware('can:show.material_units');
    Route::get('/material-units/{material_unit}', [MaterialUnitController::class, 'show'])->middleware('can:show.material_units');
    Route::get('/pricing-rule-types', [PricingRuleTypeController::class, 'index'])->middleware('can:show.pricing_rule_types');
    Route::get('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'show'])->middleware('can:show.pricing_rule_types');
    Route::get('/problem-types', [ProblemTypeController::class, 'index'])->middleware('can:show.problem_types');
    Route::get('/problem-types/{problem_type}', [ProblemTypeController::class, 'show'])->middleware('can:show.problem_types');
    Route::get('/system-settings', [SystemSettingController::class, 'index'])->middleware('can:show.system_settings');
    Route::get('/system-settings/{system_setting}', [SystemSettingController::class, 'show'])->middleware('can:show.system_settings');
    Route::get('/ai-rules', [AiRuleController::class, 'index'])->middleware('can:show.ai_rules');
    Route::get('/ai-rules/{ai_rule}', [AiRuleController::class, 'show'])->middleware('can:show.ai_rules');


    //for Customer
    Route::middleware('active.user')->group(function () {
        Route::post('bookings/quote', [BookingController::class, 'quote'])->middleware('can:create.order');
        Route::post('bookings/confirm', [BookingController::class, 'confirm'])->middleware('can:create.order');
    });

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

    Route::post('/pricing-rule-types', [PricingRuleTypeController::class, 'store'])->middleware('can:manage.pricing_rule_types');
    Route::post('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'update'])->middleware('can:manage.pricing_rule_types');
    Route::delete('/pricing-rule-types/{pricing_rule_type}', [PricingRuleTypeController::class, 'destroy'])->middleware('can:manage.pricing_rule_types');

    Route::post('/pricing-rules', [PricingRuleController::class, 'store'])->middleware('can:manage.pricing_rule');
    Route::post('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'update'])->middleware('can:manage.pricing_rule');
    Route::delete('/pricing-rules/{pricing_rule}', [PricingRuleController::class, 'destroy'])->middleware('can:manage.pricing_rule');

    Route::post('/material-units', [MaterialUnitController::class, 'store'])->middleware('can:manage.material_units');
    Route::post('/material-units/{material_unit}', [MaterialUnitController::class, 'update'])->middleware('can:manage.material_units');
    Route::delete('/material-units/{material_unit}', [MaterialUnitController::class, 'destroy'])->middleware('can:manage.material_units');

    Route::post('/materials', [MaterialController::class, 'store'])->middleware('can:add.material');
    Route::post('/materials/{material}', [MaterialController::class, 'update'])->middleware('can:edit.material');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->middleware('can:delete.material');

    Route::post('/problem-types', [ProblemTypeController::class, 'store'])->middleware('can:manage.problem_types');
    Route::post('/problem-types/{problem_type}', [ProblemTypeController::class, 'update'])->middleware('can:manage.problem_types');
    Route::delete('/problem-types/{problem_type}', [ProblemTypeController::class, 'destroy'])->middleware('can:manage.problem_types');

    Route::post('/suggested-problems', [SuggestedProblemController::class, 'store'])->middleware('can:manage.suggested_problems');
    Route::post('/suggested-problems/{suggested_problem}', [SuggestedProblemController::class, 'update'])->middleware('can:manage.suggested_problems');
    Route::delete('/suggested-problems/{suggested_problem}', [SuggestedProblemController::class, 'destroy'])->middleware('can:manage.suggested_problems');

    Route::post('/system-settings', [SystemSettingController::class, 'store'])->middleware('can:manage.system_setting');
    Route::post('/system-settings/{system_setting}', [SystemSettingController::class, 'update'])->middleware('can:manage.system_setting');
    Route::delete('/system-settings/{system_setting}', [SystemSettingController::class, 'destroy'])->middleware('can:manage.system_setting');

    Route::post('/ai-rules', [AiRuleController::class, 'store'])->middleware('can:add.ai_rule');
    Route::post('/ai-rules/{ai_rule}', [AiRuleController::class, 'update'])->middleware('can:edit.ai_rule');
    Route::delete('/ai-rules/{ai_rule}', [AiRuleController::class, 'destroy'])->middleware('can:delete.ai_rule');

    Route::post('/branches', [BranchController::class, 'store'])->middleware('can:add.branch');
    Route::post('/branches/{branch}', [BranchController::class, 'update'])->middleware('can:edit.branch');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->middleware('can:delete.branch');

    Route::get('/admins', [AdminController::class, 'index'])->middleware('can:show.admins');
    Route::get('/admins/{admin}', [AdminController::class, 'show'])->middleware('can:show.admins');
    Route::post('/admins', [AdminController::class, 'store'])->middleware('can:add.admin');
    Route::post('/admins/{admin}', [AdminController::class, 'update'])->middleware('can:edit.admin');
    Route::post('/admins/{admin}/deactivate', [AdminController::class, 'deactivate'])->middleware('can:edit.admin');
    Route::post('/admins/{admin}/activate', [AdminController::class, 'activate'])->middleware('can:edit.admin');
    Route::delete('/admins/{admin}', [AdminController::class, 'destroy'])->middleware('can:delete.admin');

    Route::post('/customers/personal/{customer}',[CustomerPersonalController::class, 'update'])->middleware('can:edit.personal_customers');
    Route::delete('/customers/personal/{customer}',[CustomerPersonalController::class, 'destroy'])->middleware('can:delete.personal_customers');

    Route::post('/customers/company/{customer}',[CustomerCompanyController::class, 'update'])->middleware('can:edit.company_customers');
    Route::delete('/customers/company/{customer}',[CustomerCompanyController::class, 'destroy'])->middleware('can:delete.company_customers');

    Route::post('/workshops', [WorkshopController::class, 'store'])->middleware('can:add.workshop');
    Route::post('/workshops/{workshop}', [WorkshopController::class, 'update'])->middleware('can:edit.workshop');
    Route::delete('/workshops/{workshop}', [WorkshopController::class, 'destroy'])->middleware('can:delete.workshop');

});
Route::bind('admin', function ($value) {
    return User::role('admin')->findOrFail($value);
});

