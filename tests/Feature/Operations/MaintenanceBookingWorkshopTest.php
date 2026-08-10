<?php

namespace Tests\Feature\Operations;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\WorkshopStatus;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Models\Workshop;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MaintenanceBookingWorkshopTest extends TestCase
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
        $carType = CarType::create(['name' => 'Sedan', 'name_ar' => 'نوع', 'price_multiplier' => 1.0, 'is_active' => true]);

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

    private function maintenanceService(float $basePrice = 100): Service
    {
        $category = Category::create(['name' => 'Maintenance', 'name_ar' => 'صيانة', 'is_active' => true]);

        return Service::create([
            'category_id' => $category->id,
            'name' => 'General Inspection',
            'name_ar' => 'فحص شامل',
            'description' => 'desc',
            'base_price' => $basePrice,
            'is_vip_available' => false,
            'duration_minutes' => 30,
        ]);
    }

    private function workshop(string $status = 'active', float $lat = 24.6330, float $lng = 46.7167): Workshop
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('workshop');

        $workshop = Workshop::create([
            'user_id' => $owner->id,
            'name' => 'Workshop ' . uniqid(),
            'name_ar' => 'ورشة',
            'address' => 'Industrial Area',
            'city' => 'Riyadh',
            'latitude' => $lat,
            'longitude' => $lng,
            'status' => $status,
            'rating_avg' => 4.5,
        ]);

        $workshop->setRelation('owner', $owner);

        return $workshop;
    }

    private function basePayload(Car $car, Service $service, ?int $workshopId, string $paymentMethod): array
    {
        return [
            'car_ids' => [$car->id],
            'service_id' => $service->id,
            'booking_type' => true,
            'payment_method' => $paymentMethod,
            'workshop_id' => $workshopId,
        ];
    }

    public function test_nearby_returns_only_active_workshops_within_radius(): void
    {
        $this->customer();
        $near = $this->workshop('active', 24.6330, 46.7167);
        $this->workshop('active', 30.0, 50.0); // far away
        $this->workshop('pending', 24.6340, 46.7177); // close but not active

        $res = $this->getJson('/api/workshops/nearby?latitude=24.6330&longitude=46.7167&radius_km=25');

        $res->assertOk();
        $ids = collect($res->json('data'))->pluck('id')->all();

        $this->assertEquals([$near->id], $ids);
        $this->assertArrayHasKey('distance_km', $res->json('data.0'));
    }

    public function test_maintenance_booking_without_workshop_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->maintenanceService();

        $res = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, null, PaymentMethod::CASH->value));

        $res->assertStatus(422)->assertJsonValidationErrors('workshop_id', 'data');
    }

    public function test_maintenance_booking_with_non_cash_payment_is_rejected(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->maintenanceService();
        $workshop = $this->workshop();

        $res = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $workshop->id, PaymentMethod::WALLET->value));

        $res->assertStatus(422)->assertJsonValidationErrors('payment_method', 'data');
    }

    public function test_maintenance_booking_succeeds_and_records_workshop(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->maintenanceService(100);
        $workshop = $this->workshop();

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $workshop->id, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');

        $res = $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']]);
        $res->assertOk();

        $order = Order::firstOrFail();
        $this->assertDatabaseHas('maintenance_details', [
            'order_id' => $order->id,
            'workshop_id' => $workshop->id,
        ]);
    }

    public function test_workshop_manager_can_view_history_of_a_car_that_visited(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $service = $this->maintenanceService(100);
        $workshop = $this->workshop();

        $quote = $this->postJson('/api/bookings/quote', $this->basePayload($car, $service, $workshop->id, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quote['quote_token']])->assertOk();

        Sanctum::actingAs($workshop->owner);

        $res = $this->getJson("/api/workshops/cars/{$car->id}/history");

        $res->assertOk();
        $this->assertCount(1, $res->json('data'));
    }

    public function test_workshop_manager_cannot_view_history_of_a_car_that_never_visited(): void
    {
        $customer = $this->customer();
        $car = $this->carFor($customer);
        $workshop = $this->workshop();

        Sanctum::actingAs($workshop->owner);

        $res = $this->getJson("/api/workshops/cars/{$car->id}/history");

        $res->assertStatus(403);
    }

    public function test_bookings_index_scoped_to_workshop_only_shows_its_own_bookings(): void
    {
        $customer = $this->customer();
        $service = $this->maintenanceService(100);

        $workshopA = $this->workshop('active', 24.6330, 46.7167);
        $carA = $this->carFor($customer);
        $quoteA = $this->postJson('/api/bookings/quote', $this->basePayload($carA, $service, $workshopA->id, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quoteA['quote_token']])->assertOk();

        $workshopB = $this->workshop('active', 24.7136, 46.6753);
        $carB = $this->carFor($customer);
        $quoteB = $this->postJson('/api/bookings/quote', $this->basePayload($carB, $service, $workshopB->id, PaymentMethod::CASH->value))
            ->assertOk()
            ->json('data');
        $this->postJson('/api/bookings/confirm', ['quote_token' => $quoteB['quote_token']])->assertOk();

        Sanctum::actingAs($workshopA->owner);

        $res = $this->getJson('/api/bookings/');

        $res->assertOk();
        $orderIds = collect($res->json('data'))->pluck('id')->all();

        $this->assertDatabaseHas('maintenance_details', ['workshop_id' => $workshopA->id]);
        $this->assertNotEmpty($orderIds);
        foreach ($orderIds as $orderId) {
            $this->assertDatabaseHas('maintenance_details', [
                'order_id' => $orderId,
                'workshop_id' => $workshopA->id,
            ]);
        }
    }

    public function test_bookings_index_for_workshop_without_a_profile_returns_empty(): void
    {
        $owner = User::factory()->create(['is_active' => true]);
        $owner->assignRole('workshop');
        Sanctum::actingAs($owner);

        $res = $this->getJson('/api/bookings/');

        $res->assertOk();
        $this->assertCount(0, $res->json('data'));
    }
}