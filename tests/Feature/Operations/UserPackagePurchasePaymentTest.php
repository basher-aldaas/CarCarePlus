<?php

namespace Tests\Feature\Operations;

use App\Enums\PackageType;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Wallet;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPackagePurchasePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function customer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_personal');

        return $user;
    }

    private function walletFor(User $user, float $balance): Wallet
    {
        return Wallet::create(['user_id' => $user->id, 'balance' => $balance]);
    }

    private function package(float $price = 200): Package
    {
        return Package::create([
            'name' => 'Package ' . uniqid(),
            'description' => 'desc',
            'type' => PackageType::MONTHLY->value,
            'is_company_package' => false,
            'price' => $price,
            'discount_pct' => 0,
            'services_count' => 5,
            'valid_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_customer_subscription_debits_wallet_and_records_a_paid_payment(): void
    {
        $customer = $this->customer();
        $this->walletFor($customer, 500);
        $package = $this->package(200);

        Sanctum::actingAs($customer);

        $res = $this->postJson('/api/user-packages', ['package_id' => $package->id]);

        $res->assertOk();

        // Subscription created with the plan's service count.
        $this->assertDatabaseHas('user_packages', [
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'remaining_count' => 5,
        ]);

        // Wallet debited 200 -> 300, with a transaction logged.
        $this->assertEquals(300.0, (float) Wallet::where('user_id', $customer->id)->value('balance'));
        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $customer->id,
            'type' => 'debit',
            'amount' => 200.00,
        ]);

        // The payment row lives in the payments table.
        $this->assertDatabaseHas('payments', [
            'user_id' => $customer->id,
            'package_id' => $package->id,
            'type' => PaymentType::PACKAGE->value,
            'method' => PaymentMethod::WALLET->value,
            'status' => PaymentStatus::PAID->value,
            'amount' => 200.00,
        ]);
    }

    public function test_insufficient_wallet_balance_blocks_subscription_and_payment(): void
    {
        $customer = $this->customer();
        $this->walletFor($customer, 50);
        $package = $this->package(200);

        Sanctum::actingAs($customer);

        $this->postJson('/api/user-packages', ['package_id' => $package->id])
            ->assertStatus(422);

        // Nothing settled: no subscription, no payment, balance untouched.
        $this->assertDatabaseMissing('user_packages', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('payments', ['user_id' => $customer->id]);
        $this->assertEquals(50.0, (float) Wallet::where('user_id', $customer->id)->value('balance'));
    }

    public function test_staff_assignment_is_free_and_records_no_payment(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        $customer = $this->customer();
        $this->walletFor($customer, 500);
        $package = $this->package(200);

        Sanctum::actingAs($admin);

        $this->postJson("/api/user-packages/{$customer->id}", ['package_id' => $package->id])
            ->assertOk();

        $this->assertDatabaseHas('user_packages', [
            'user_id' => $customer->id,
            'package_id' => $package->id,
        ]);

        // Admin assignment must not charge the customer.
        $this->assertDatabaseMissing('payments', ['user_id' => $customer->id]);
        $this->assertEquals(500.0, (float) Wallet::where('user_id', $customer->id)->value('balance'));
    }
}