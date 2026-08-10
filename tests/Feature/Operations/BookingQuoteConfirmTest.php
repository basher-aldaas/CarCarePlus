<?php

namespace Tests\Feature\Operations;

use App\Enums\OrderEnums\OrderStatus;
use App\Enums\PaymentEnums\PaymentMethod;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * End-to-end coverage of the two-step booking flow's two endpoints:
 *   POST /api/bookings/quote   — validates + prices a submission, caches it
 *                                under a short-lived single-use quote_token
 *   POST /api/bookings/confirm — redeems that token and creates the order(s)
 *
 * The pricing/coverage maths for sub-services, materials, packages, company
 * multi-car grouping and per-service-type handlers are covered by their own
 * dedicated tests. This file focuses on the endpoints themselves: request
 * validation, the quote_token lifecycle (issue → redeem → single-use →
 * expiry → ownership), branch/ownership gating and the scheduled-vs-immediate
 * scheduling rules.
 */
class BookingQuoteConfirmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    // ---- Fixtures ----------------------------------------------------------

    private function customer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_personal');
        Sanctum::actingAs($user);

        return $user;
    }

    private function companyCustomer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('customer_company');

        \App\Models\Company::create([
            'customer_id' => $user->id,
            'name' => 'Company ' . uniqid(),
            'name_ar' => 'شركة',
            'commercial_reg' => (string) random_int(1000000000, 9999999999),
            'tax_number' => '15' . random_int(1000000000, 9999999999),
            'address' => 'Address',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    private function carFor(User $customer): Car
    {
        $brand = CarBrand::create(['name' => 'Brand ' . uniqid(), 'is_active' => true]);
        $carType = CarType::create(['name' => 'Sedan', 'name_ar' => 'سيدان', 'price_multiplier' => 1, 'is_active' => true]);

        return Car::create([
            'user_id' => $customer->id,
            'brand_id' => $brand->id,
            'car_type_id' => $carType->id,
            'plate_number' => 'ABC-' . uniqid(),
            'model' => 'Camry',
            'year' => 2022,
            'color' => 'White',
            'fuel_type' => 'petrol',
            'cylinders' => 4,
            'mileage' => 10000,
            'is_active' => true,
        ]);
    }

    private function service(float $basePrice = 100): Service
    {
        $category = Category::create(['name' => 'Category ' . uniqid(), 'name_ar' => 'فئة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'Basic Wash',
            'name_ar' => 'غسيل أساسي',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);
    }

    private function branch(bool $active = true): Branch
    {
        $admin = User::factory()->create(['is_active' => true]);

        return Branch::create([
            'admin_id' => $admin->id,
            'name' => 'Branch ' . uniqid(),
            'name_ar' => 'فرع',
            'city' => 'City',
            'address' => 'Address',
            'phone' => '0000000000',
            'is_active' => $active,
        ]);
    }

    private function basePayload(array $carIds, Service $service, ?Branch $branch = null): array
    {
        return [
            'car_ids' => $carIds,
            'service_id' => $service->id,
            'branch_id' => $branch?->id,
            'booking_type' => false,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'payment_method' => PaymentMethod::CASH->value,
        ];
    }

    // ---- Quote: happy path -------------------------------------------------

    public function test_quote_issues_a_token_with_price_and_expiry(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $res = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service));

        $res->assertOk();
        $this->assertEquals(100.0, (float) $res->json('data.total_price'));
        $res->assertJsonPath('data.car_count', 1);
        $this->assertNotEmpty($res->json('data.quote_token'));
        $this->assertNotEmpty($res->json('data.expires_at'));

        // quoting must not create any order — it only caches the priced payload
        $this->assertDatabaseCount('orders', 0);
    }

    // ---- Quote: request validation (CreateBookingRequest) ------------------

    public function test_quote_requires_car_ids(): void
    {
        $this->customer();
        $service = $this->service();

        $payload = $this->basePayload([], $service);
        unset($payload['car_ids']);

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('car_ids', 'data');
    }

    public function test_quote_requires_an_existing_service(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['service_id'] = 999999;

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('service_id', 'data');
    }

    public function test_quote_requires_a_valid_payment_method(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['payment_method'] = 'bitcoin';

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_method', 'data');
    }

    public function test_quote_requires_booking_type(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        unset($payload['booking_type']);

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('booking_type', 'data');
    }

    public function test_scheduled_booking_requires_scheduled_at(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['booking_type'] = false; // scheduled
        $payload['scheduled_at'] = null;

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheduled_at', 'data');
    }

    public function test_immediate_booking_prohibits_scheduled_at(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['booking_type'] = true; // immediate
        $payload['scheduled_at'] = now()->addDay()->toDateTimeString();

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheduled_at', 'data');
    }

    public function test_scheduled_at_must_not_be_in_the_past(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['scheduled_at'] = now()->subDay()->toDateTimeString();

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheduled_at', 'data');
    }

    public function test_location_lat_requires_location_lng(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $payload = $this->basePayload([$car->id], $service);
        $payload['location_lat'] = 24.7;
        // location_lng deliberately omitted

        $this->postJson('/api/bookings/quote', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('location_lng', 'data');
    }

    // ---- Quote: ownership & gating -----------------------------------------

    public function test_personal_customer_cannot_book_more_than_one_car(): void
    {
        $customer = $this->customer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service();

        $this->postJson('/api/bookings/quote', $this->basePayload([$carA->id, $carB->id], $service))
            ->assertStatus(422)
            ->assertJsonValidationErrors('car_ids', 'data');
    }

    public function test_customer_cannot_quote_a_car_they_do_not_own(): void
    {
        $customer = $this->customer();

        $stranger = User::factory()->create(['is_active' => true]);
        $foreignCar = $this->carFor($stranger);
        $service = $this->service();

        $this->postJson('/api/bookings/quote', $this->basePayload([$foreignCar->id], $service))
            ->assertStatus(422)
            ->assertJsonValidationErrors('car_ids', 'data');
    }

    public function test_quote_against_an_inactive_branch_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $branch = $this->branch(active: false);

        // BookingBranchInactiveException renders as 403 Forbidden
        $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service, $branch))
            ->assertStatus(403);
    }

    public function test_quote_requires_authentication(): void
    {
        $service = $this->service();

        $this->postJson('/api/bookings/quote', [
            'car_ids' => [1],
            'service_id' => $service->id,
            'booking_type' => false,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'payment_method' => PaymentMethod::CASH->value,
        ])->assertStatus(401);
    }

    // ---- Confirm: happy path -----------------------------------------------

    public function test_confirm_creates_a_pending_order_from_a_valid_token(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service))
            ->assertOk()
            ->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);

        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals($customer->id, $order->customer_id);
        $this->assertEquals($car->id, $order->car_id);
        $this->assertEquals(OrderStatus::PENDING->value, $order->status->value ?? $order->status);
        $this->assertEquals(100.0, (float) $order->total_price);
        // a personal (non-company) booking carries no group id
        $this->assertNull($order->booking_group_id);
    }

    // ---- Confirm: request validation ---------------------------------------

    public function test_confirm_requires_a_quote_token(): void
    {
        $this->customer();

        $this->postJson('/api/bookings/confirm', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quote_token', 'data');
    }

    public function test_confirm_with_an_unknown_token_is_rejected(): void
    {
        $this->customer();

        $this->postJson('/api/bookings/confirm', ['quote_token' => 'not-a-real-token'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quote_token', 'data');
    }

    // ---- Confirm: token lifecycle ------------------------------------------

    public function test_a_quote_token_can_only_be_confirmed_once(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service))
            ->assertOk()
            ->json('data');

        // first confirm succeeds and consumes the token (Cache::pull)
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        // second confirm with the same token must fail — no duplicate order
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quote_token', 'data');

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_an_expired_quote_token_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service))
            ->assertOk()
            ->json('data');

        // simulate TTL expiry by evicting the cached payload
        Cache::forget($quote['quote_token']);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quote_token', 'data');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_customer_cannot_confirm_another_customers_token(): void
    {
        // customer A takes a quote
        $customerA = $this->customer();
        $car = $this->carFor($customerA);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service))
            ->assertOk()
            ->json('data');

        // customer B (now the acting user) tries to redeem A's token
        $customerB = User::factory()->create(['is_active' => true]);
        $customerB->assignRole('customer_personal');
        Sanctum::actingAs($customerB);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('quote_token', 'data');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_confirm_requires_authentication(): void
    {
        // build a valid token as a real customer first
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$car->id], $service))
            ->assertOk()
            ->json('data');

        // drop authentication, then attempt to confirm
        app('auth')->forgetGuards();

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])
            ->assertStatus(401);
    }

    // ---- Confirm: scheduling rules -----------------------------------------

    public function test_scheduled_booking_preserves_the_chosen_time(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $scheduledAt = now()->addDays(2)->setTime(14, 0);

        $payload = $this->basePayload([$car->id], $service);
        $payload['scheduled_at'] = $scheduledAt->toDateTimeString();

        $quote = $this->postJson('/api/bookings/quote', $payload)->assertOk()->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals($scheduledAt->toDateTimeString(), $order->scheduled_at->toDateTimeString());
    }

    public function test_immediate_booking_is_scheduled_shortly_after_confirmation(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        $payload = $this->basePayload([$car->id], $service);
        $payload['booking_type'] = true; // immediate
        $payload['scheduled_at'] = null;

        $quote = $this->postJson('/api/bookings/quote', $payload)->assertOk()->json('data');

        $confirmedAt = now();
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        // immediate bookings are scheduled 60 minutes from confirmation time
        $this->assertEqualsWithDelta(
            $confirmedAt->copy()->addMinutes(60)->timestamp,
            $order->scheduled_at->timestamp,
            60,
        );
    }

    // ---- Company grouping quick check --------------------------------------

    public function test_company_customer_can_quote_and_confirm_multiple_cars_as_one_group(): void
    {
        $customer = $this->companyCustomer();
        $carA = $this->carFor($customer);
        $carB = $this->carFor($customer);
        $service = $this->service(100);

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload([$carA->id, $carB->id], $service))
            ->assertOk()
            ->json('data');

        $this->assertEquals(2, $quote['car_count']);
        $this->assertEquals(200.0, (float) $quote['total_price']);

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $orders = Order::all();
        $this->assertCount(2, $orders);
        $this->assertCount(1, $orders->pluck('booking_group_id')->unique());
        $this->assertNotNull($orders->first()->booking_group_id);
    }
}