<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use App\Models\Workshop;
use App\Notifications\RegistrationApprovedNotification;
use App\Notifications\RegistrationPendingNotification;
use App\Notifications\RegistrationRejectedNotification;
use App\Notifications\StaffAccountCreatedNotification;
use App\Notifications\WelcomeNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Notification::fake();
    }

    private function actAsSuperAdmin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        Sanctum::actingAs($admin);
    }

    public function test_customer_receives_welcome_email(): void
    {
        $this->postJson('/api/auth/register/customer', [
            'name'                  => 'Ahmed',
            'email'                 => 'ahmed@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $user = User::where('email', 'ahmed@example.com')->first();
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_company_owner_receives_pending_email(): void
    {
        $this->postJson('/api/auth/register/company', [
            'name'                  => 'Owner',
            'email'                 => 'owner@corp.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'company_name'          => 'Fleet Co',
            'company_name_ar'       => 'شركة الأسطول',
            'commercial_reg'        => 'CR-1',
            'tax_number'            => 'TAX-1',
            'company_address'       => 'Riyadh',
        ])->assertCreated();

        $user = User::where('email', 'owner@corp.com')->first();
        Notification::assertSentTo($user, RegistrationPendingNotification::class);
    }

    public function test_workshop_owner_receives_pending_email(): void
    {
        $this->postJson('/api/auth/register/workshop', [
            'name'                  => 'WS Owner',
            'email'                 => 'ws@shop.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'workshop_name'         => 'Excellence WS',
            'workshop_name_ar'      => 'ورشة التميز',
            'workshop_address'      => 'Industrial Area',
            'workshop_city'         => 'Riyadh',
            'latitude'              => 24.6333,
            'longitude'             => 46.7167,
        ])->assertCreated();

        $user = User::where('email', 'ws@shop.com')->first();
        Notification::assertSentTo($user, RegistrationPendingNotification::class);
    }

    public function test_owner_receives_approved_email_on_approval(): void
    {
        $owner = User::factory()->create(['is_active' => false]);
        $owner->assignRole('customer_company');
        $company = Company::create([
            'customer_id'    => $owner->id,
            'name'           => 'Fleet Co',
            'name_ar'        => 'شركة',
            'commercial_reg' => 'CR-9',
            'tax_number'     => 'TAX-9',
            'address'        => 'Riyadh',
        ]);

        $this->actAsSuperAdmin();
        $this->postJson("/api/admin/registration-requests/companies/{$company->id}/approve")
            ->assertOk();

        Notification::assertSentTo($owner, RegistrationApprovedNotification::class);
    }

    public function test_owner_receives_rejected_email_with_reason(): void
    {
        $owner = User::factory()->create(['is_active' => false]);
        $owner->assignRole('workshop');
        $workshop = Workshop::create([
            'user_id' => $owner->id,
            'name'    => 'WS',
            'name_ar' => 'ورشة',
            'address' => 'Area',
            'city'    => 'Riyadh',
            'status'  => 'pending',
        ]);

        $this->actAsSuperAdmin();
        $this->postJson("/api/admin/registration-requests/workshops/{$workshop->id}/reject", [
            'reason' => 'Incomplete documents',
        ])->assertOk();

        Notification::assertSentTo(
            $owner,
            RegistrationRejectedNotification::class,
            fn (RegistrationRejectedNotification $n) => $n->reason === 'Incomplete documents'
        );
    }

    public function test_staff_receives_account_created_email(): void
    {
        $this->actAsSuperAdmin();

        $branchManager = User::factory()->create();
        $branch = \App\Models\Branch::create([
            'admin_id' => $branchManager->id,
            'name'     => 'Main',
            'name_ar'  => 'الرئيسي',
            'city'     => 'Riyadh',
            'address'  => 'Center',
            'phone'    => '0500000000',
        ]);

        $this->postJson('/api/admin/employees', [
            'name'      => 'Tech',
            'email'     => 'tech@system.com',
            'phone'     => '0512345681',
            'password'  => 'password123',
            'branch_id' => $branch->id,
            'type'      => 'washer',
        ])->assertCreated();

        $user = User::where('email', 'tech@system.com')->first();
        Notification::assertSentTo($user, StaffAccountCreatedNotification::class);
    }

    /** Content smoke test — templates render without error and carry key data. */
    public function test_notification_mail_templates_render(): void
    {
        $user = User::factory()->make(['name' => 'Sara', 'email' => 'sara@example.com']);

        $welcome = (new WelcomeNotification())->toMail($user);
        $this->assertNotEmpty($welcome->subject);

        $pending = (new RegistrationPendingNotification('company'))->toMail($user);
        $this->assertNotEmpty($pending->subject);

        $rejected = (new RegistrationRejectedNotification('workshop', 'Bad docs'))->toMail($user);
        $this->assertTrue(collect($rejected->introLines)->contains(fn ($l) => str_contains($l, 'Bad docs')));

        $staff = (new StaffAccountCreatedNotification('employee'))->toMail($user);
        $this->assertTrue(collect($staff->introLines)->contains(fn ($l) => str_contains($l, 'sara@example.com')));
    }
}
