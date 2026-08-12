<?php

namespace Tests\Feature\Operations;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Support\GeoDistance;
use Database\Seeders\PricingRuleSeeder;
use Database\Seeders\PricingRuleTypeSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The customer sends their location; the system auto-selects the nearest
 * active branch (unless the customer picked one) and derives distance_km
 * from the customer -> branch distance. distance_km is never accepted from
 * the client and feeds the Extra Distance Charge pricing rule.
 */
class BranchSelectionDistanceTest extends TestCase
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

    private function branch(float $lat, float $lng, bool $active = true): Branch
    {
        $admin = User::factory()->create(['is_active' => true]);

        return Branch::create([
            'admin_id' => $admin->id,
            'name' => 'Branch ' . uniqid(),
            'name_ar' => 'فرع',
            'city' => 'City',
            'address' => 'Address',
            'latitude' => $lat,
            'longitude' => $lng,
            'phone' => '0000000000',
            'is_active' => $active,
        ]);
    }

    private function basePayload(Car $car, Service $service): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'booking_type' => false,
            'scheduled_at' => now()->next(\Carbon\Carbon::MONDAY)->setTime(10, 0)->toDateTimeString(),
            'payment_method' => PaymentMethod::CASH->value,
        ];
    }

    public function test_location_auto_selects_the_nearest_active_branch(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $near = $this->branch(24.7200, 46.6800);
        $far = $this->branch(25.5000, 47.5000);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
        ]);

        $res->assertOk();
        $res->assertJsonPath('data.branch.id', $near->id);

        $expected = GeoDistance::km(24.7136, 46.6753, 24.7200, 46.6800);
        $this->assertEquals($expected, (float) $res->json('data.distance_km'));
    }

    public function test_nearest_selection_skips_inactive_branches(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $this->branch(24.7140, 46.6760, active: false); // closest but inactive
        $activeFarther = $this->branch(24.7300, 46.6900, active: true);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
        ]);

        $res->assertOk();
        $res->assertJsonPath('data.branch.id', $activeFarther->id);
    }

    public function test_customer_branch_choice_overrides_nearest(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();

        $this->branch(24.7140, 46.6760); // nearest
        $chosen = $this->branch(25.0000, 47.0000); // farther, explicitly chosen

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'branch_id' => $chosen->id,
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
        ]);

        $res->assertOk();
        $res->assertJsonPath('data.branch.id', $chosen->id);

        $expected = GeoDistance::km(24.7136, 46.6753, 25.0000, 47.0000);
        $this->assertEquals($expected, (float) $res->json('data.distance_km'));
    }

    public function test_confirm_persists_auto_selected_branch_and_distance(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service();
        $near = $this->branch(24.7200, 46.6800);

        $quote = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
        ])->assertOk()->json('data');

        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        $order = Order::firstOrFail();
        $this->assertEquals($near->id, $order->branch_id);
        $this->assertEquals((float) $quote['distance_km'], (float) $order->distance_km);
    }

    public function test_distance_beyond_included_km_adds_the_extra_distance_charge(): void
    {
        $this->seed(PricingRuleTypeSeeder::class);
        $this->seed(PricingRuleSeeder::class); // Extra Distance Charge: included 20km, 0.5/km

        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);

        // ~111km north of the customer, well beyond the 20km allowance
        $branch = $this->branch(25.7136, 46.6753);

        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'location_lat' => 24.7136,
            'location_lng' => 46.6753,
        ])->assertOk();

        $distance = (float) $res->json('data.distance_km');
        $this->assertGreaterThan(20, $distance);

        $expectedExtra = round((($distance - 20) * 0.5), 2);
        $this->assertEquals(round(100 + $expectedExtra, 2), (float) $res->json('data.invoice.0.service_price'));

        // the charge is itemised on the price breakdown
        $labels = collect($res->json('data.invoice.0.price_items'))->pluck('label');
        $this->assertTrue($labels->contains('Extra Distance Charge'));
    }

    public function test_distance_km_is_not_trusted_from_the_client(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->service(100);
        $branch = $this->branch(24.7200, 46.6800);

        // client tries to inject a bogus distance; with no location sent the
        // system has nothing to derive from, so distance stays null
        $res = $this->postJson('/api/bookings/quote', [
            ...$this->basePayload($car, $service),
            'branch_id' => $branch->id,
            'location_address' => 'King Fahd Road, Riyadh',
            'distance_km' => 9999,
        ]);

        $res->assertOk();
        $this->assertNull($res->json('data.distance_km'));
    }
}