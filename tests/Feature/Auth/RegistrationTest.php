<?php

namespace Tests\Feature\Auth;

use App\Enums\CompanyStatus;
use App\Enums\EmployeeType;
use App\Enums\WorkshopStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Workshop;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        Sanctum::actingAs($user);

        return $user;
    }

    private function branch(): Branch
    {
        $manager = User::factory()->create();

        return Branch::create([
            'admin_id' => $manager->id,
            'name'     => 'Main Branch',
            'name_ar'  => 'الفرع الرئيسي',
            'city'     => 'Riyadh',
            'address'  => 'Center',
            'phone'    => '0500000000',
        ]);
    }

    /** Type 1 — personal customer self-registers and is active immediately. */
    public function test_customer_registers_and_receives_token(): void
    {
        $res = $this->postJson('/api/auth/register/customer', [
            'name'                  => 'Ahmed',
            'email'                 => 'ahmed@example.com',
            'phone'                 => '0512345678',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertCreated()
            ->assertJsonPath('status', 1)
            ->assertJsonPath('data.email', 'ahmed@example.com')
            ->assertJsonStructure(['data' => ['token']]);

        $user = User::where('email', 'ahmed@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('customer_personal'));
    }

    /** Type 2a — company submits a pending request; user is inactive. */
    public function test_company_registration_creates_pending_request(): void
    {
        $res = $this->postJson('/api/auth/register/company', [
            'name'                  => 'Owner',
            'email'                 => 'owner@corp.com',
            'phone'                 => '0512345679',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'company_name'          => 'Fleet Co',
            'company_name_ar'       => 'شركة الأسطول',
            'commercial_reg'        => 'CR-123',
            'tax_number'            => 'TAX-123',
            'company_address'       => 'Riyadh',
        ]);

        $res->assertCreated()->assertJsonMissingPath('data.token');

        $user = User::where('email', 'owner@corp.com')->first();
        $this->assertFalse($user->is_active);
        $this->assertTrue($user->hasRole('customer_company'));

        $company = Company::where('customer_id', $user->id)->first();
        $this->assertSame(CompanyStatus::PENDING, $company->status);
        $this->assertFalse($company->is_active);
    }

    /** Type 2b — workshop submits a pending request linked to its owner. */
    public function test_workshop_registration_creates_pending_request(): void
    {
        $res = $this->postJson('/api/auth/register/workshop', [
            'name'                  => 'WS Owner',
            'email'                 => 'ws@shop.com',
            'phone'                 => '0512345680',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'workshop_name'         => 'Excellence WS',
            'workshop_name_ar'      => 'ورشة التميز',
            'workshop_address'      => 'Industrial Area',
            'workshop_city'         => 'Riyadh',
            'latitude'              => 24.6333,
            'longitude'             => 46.7167,
        ]);

        $res->assertCreated();

        $user = User::where('email', 'ws@shop.com')->first();
        $this->assertFalse($user->is_active);
        $this->assertTrue($user->hasRole('workshop'));

        $workshop = Workshop::where('user_id', $user->id)->first();
        $this->assertNotNull($workshop);
        $this->assertSame(WorkshopStatus::PENDING, $workshop->status);
    }

    /** Type 3a — super admin creates a mechanic employee. */
    public function test_super_admin_creates_employee(): void
    {
        $this->superAdmin();
        $branch = $this->branch();

        $res = $this->postJson('/api/admin/employees', [
            'name'      => 'Tech Guy',
            'email'     => 'tech@system.com',
            'phone'     => '0512345681',
            'password'  => 'password123',
            'branch_id' => $branch->id,
            'type'      => 'mechanic',
        ]);

        $res->assertCreated();

        $user = User::where('email', 'tech@system.com')->first();
        $this->assertTrue($user->hasRole('employee_mechanic'));
        $this->assertDatabaseHas('employees', [
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'type'      => EmployeeType::MECHANIC->value,
        ]);
    }

    /** Type 3b — super admin creates a branch admin (type=admin) and assigns management. */
    public function test_super_admin_creates_admin_and_assigns_branch(): void
    {
        $this->superAdmin();
        $branch = $this->branch();

        $res = $this->postJson('/api/admin/employees', [
            'name'      => 'Branch Boss',
            'email'     => 'boss@system.com',
            'phone'     => '0512345682',
            'password'  => 'password123',
            'branch_id' => $branch->id,
            'type'      => 'admin',
        ]);

        $res->assertCreated();

        $user = User::where('email', 'boss@system.com')->first();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertDatabaseHas('employees', [
            'user_id'   => $user->id,
            'branch_id' => $branch->id,
            'type'      => EmployeeType::ADMIN->value,
        ]);
        $this->assertDatabaseHas('branches', [
            'id'       => $branch->id,
            'admin_id' => $user->id,
        ]);
    }

    /** Approval — super admin approves a pending company; owner becomes active. */
    public function test_super_admin_approves_company_request(): void
    {
        // Create a pending company via the public endpoint first.
        $this->postJson('/api/auth/register/company', [
            'name'                  => 'Owner',
            'email'                 => 'owner2@corp.com',
            'phone'                 => '0512345683',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'company_name'          => 'Fleet Two',
            'company_name_ar'       => 'شركة اثنان',
            'commercial_reg'        => 'CR-2',
            'tax_number'            => 'TAX-2',
            'company_address'       => 'Riyadh',
        ])->assertCreated();

        $company = Company::first();

        $this->superAdmin();
        $this->postJson("/api/admin/registration-requests/companies/{$company->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', CompanyStatus::APPROVED->value);

        $company->refresh();
        $this->assertSame(CompanyStatus::APPROVED, $company->status);
        $this->assertTrue($company->owner->is_active);
    }

    /** Access control — a customer cannot reach super-admin endpoints. */
    public function test_non_super_admin_is_forbidden_from_admin_endpoints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer_personal');
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/registration-requests/companies')->assertForbidden();
    }

    /** Access control — unauthenticated requests are rejected. */
    public function test_guest_cannot_reach_admin_endpoints(): void
    {
        $this->getJson('/api/admin/registration-requests/companies')->assertUnauthorized();
    }
}
