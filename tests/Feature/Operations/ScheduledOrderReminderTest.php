<?php

namespace Tests\Feature\Operations;

use App\Enums\EmployeeType;
use App\Enums\OrderEnums\OrderStatus;
use App\Models\Branch;
use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarType;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScheduledOrderReminderTest extends TestCase
{
    use RefreshDatabase;

    private Car $car;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $brand = CarBrand::create(['name' => 'Toyota', 'is_active' => true]);
        $type = CarType::create(['name' => 'Sedan', 'name_ar' => 'سيدان', 'price_multiplier' => 1, 'is_active' => true]);

        $this->car = Car::create([
            'user_id' => User::factory()->create()->id,
            'brand_id' => $brand->id,
            'car_type_id' => $type->id,
            'plate_number' => 'ABC-1234',
            'model' => 'Camry',
            'year' => 2020,
            'color' => 'white',
            'fuel_type' => 'petrol',
            'cylinders' => 4,
            'mileage' => 50000,
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Maintenance', 'name_ar' => 'صيانة', 'is_active' => true]);

        $this->service = Service::create([
            'category_id' => $category->id,
            'name' => 'Oil Change',
            'name_ar' => 'تغيير زيت',
            'description' => 'desc',
            'base_price' => 100,
            'is_vip_available' => false,
            'duration_minutes' => 60,
        ]);
    }

    private function makeEmployee(?User $user = null): Employee
    {
        $user ??= User::factory()->create();
        $branch = Branch::create([
            'admin_id' => User::factory()->create()->id,
            'name' => 'Branch ' . uniqid(),
            'name_ar' => 'فرع',
            'city' => 'City',
            'address' => 'Address',
            'latitude' => 24.0,
            'longitude' => 46.0,
            'phone' => '0500000000',
            'is_active' => true,
            'working_hours' => json_encode(['mon' => '08:00-20:00']),
            'is_24h' => false,
        ]);

        return Employee::create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'type' => EmployeeType::MECHANIC->value,
            'is_active' => true,
            'rating_avg' => 5,
        ]);
    }

    private function makeOrder(Carbon $scheduledAt, string $status = OrderStatus::ASSIGNED->value, ?Employee $employee = null): Order
    {
        return Order::create([
            'customer_id' => User::factory()->create()->id,
            'car_id' => $this->car->id,
            'employee_id' => $employee?->id,
            'category_id' => $this->service->category_id,
            'service_id' => $this->service->id,
            'booking_type' => false,
            'is_vip' => false,
            'scheduled_at' => $scheduledAt,
            'total_price' => 100,
            'status' => $status,
        ]);
    }

    private function runReminders(): void
    {
        $this->artisan('orders:send-reminders')->assertSuccessful();
    }

    public function test_customer_is_reminded_when_appointment_is_within_30_minutes(): void
    {
        $order = $this->makeOrder(now()->addMinutes(20));

        $this->runReminders();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $order->customer_id,
            'reference_type' => 'order_customer_reminder',
            'reference_id' => $order->id,
            'type' => 'info',
        ]);
    }

    public function test_customer_is_not_reminded_when_appointment_is_more_than_30_minutes_away(): void
    {
        $order = $this->makeOrder(now()->addMinutes(45));

        $this->runReminders();

        $this->assertDatabaseMissing('notifications', [
            'reference_type' => 'order_customer_reminder',
            'reference_id' => $order->id,
        ]);
    }

    public function test_employee_is_reminded_when_appointment_is_within_1_hour(): void
    {
        $employeeUser = User::factory()->create();
        $employee = $this->makeEmployee($employeeUser);
        $order = $this->makeOrder(now()->addMinutes(45), employee: $employee);

        $this->runReminders();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $employeeUser->id,
            'reference_type' => 'order_employee_reminder',
            'reference_id' => $order->id,
            'type' => 'info',
        ]);
    }

    public function test_employee_is_not_reminded_when_appointment_is_more_than_1_hour_away(): void
    {
        $employee = $this->makeEmployee();
        $order = $this->makeOrder(now()->addMinutes(90), employee: $employee);

        $this->runReminders();

        $this->assertDatabaseMissing('notifications', [
            'reference_type' => 'order_employee_reminder',
            'reference_id' => $order->id,
        ]);
    }

    public function test_no_employee_reminder_for_unassigned_order(): void
    {
        $order = $this->makeOrder(now()->addMinutes(30), status: OrderStatus::PENDING->value, employee: null);

        $this->runReminders();

        $this->assertDatabaseMissing('notifications', [
            'reference_type' => 'order_employee_reminder',
            'reference_id' => $order->id,
        ]);
    }

    public function test_completed_and_cancelled_orders_are_not_reminded(): void
    {
        $completed = $this->makeOrder(now()->addMinutes(20), status: OrderStatus::COMPLETED->value);
        $cancelled = $this->makeOrder(now()->addMinutes(20), status: OrderStatus::CANCELLED->value);

        $this->runReminders();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_past_appointments_are_not_reminded(): void
    {
        $order = $this->makeOrder(now()->subMinutes(10));

        $this->runReminders();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_reminders_are_not_sent_twice(): void
    {
        $employee = $this->makeEmployee();
        $order = $this->makeOrder(now()->addMinutes(20), employee: $employee);

        $this->runReminders();
        $this->runReminders();

        // One customer + one employee reminder, no duplicates on the second run.
        $this->assertEquals(1, Notification::where('reference_type', 'order_customer_reminder')->where('reference_id', $order->id)->count());
        $this->assertEquals(1, Notification::where('reference_type', 'order_employee_reminder')->where('reference_id', $order->id)->count());
    }
}