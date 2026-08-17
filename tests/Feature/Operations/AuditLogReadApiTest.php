<?php

namespace Tests\Feature\Operations;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogReadApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super_admin');

        return $user;
    }

    private function auditLog(User $actor, string $table, string $action, array $extra = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor->id,
            'record_id' => $extra['record_id'] ?? 1,
            'table_name' => $table,
            'action' => $action,
            'old_values' => $extra['old_values'] ?? null,
            'new_values' => $extra['new_values'] ?? null,
            'ip_address' => $extra['ip_address'] ?? '127.0.0.1',
            'user_agent' => $extra['user_agent'] ?? 'PHPUnit',
        ]);
    }

    public function test_index_lists_audit_logs_newest_first_with_pagination(): void
    {
        $admin = $this->superAdmin();
        $first = $this->auditLog($admin, 'orders', 'created');
        $second = $this->auditLog($admin, 'payments', 'updated');

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/audit-logs');

        $res->assertOk();
        $res->assertJsonCount(2, 'data');
        // latest('id') => the most recently inserted row comes first.
        $res->assertJsonPath('data.0.id', $second->id);
        $res->assertJsonPath('data.1.id', $first->id);
        $res->assertJsonPath('pagination.total', 2);
    }

    public function test_index_filters_by_table_name(): void
    {
        $admin = $this->superAdmin();
        $this->auditLog($admin, 'orders', 'created');
        $paymentLog = $this->auditLog($admin, 'payments', 'created');

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/audit-logs?table_name=payments');

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.id', $paymentLog->id);
        $res->assertJsonPath('data.0.table_name', 'payments');
    }

    public function test_index_filters_by_action(): void
    {
        $admin = $this->superAdmin();
        $this->auditLog($admin, 'orders', 'created');
        $deleted = $this->auditLog($admin, 'orders', 'deleted');

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/audit-logs?action=deleted');

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.id', $deleted->id);
    }

    public function test_index_filters_by_user_id(): void
    {
        $admin = $this->superAdmin();
        $otherActor = User::factory()->create(['is_active' => true]);

        $this->auditLog($admin, 'orders', 'created');
        $otherLog = $this->auditLog($otherActor, 'orders', 'created');

        Sanctum::actingAs($admin);

        $res = $this->getJson('/api/audit-logs?user_id=' . $otherActor->id);

        $res->assertOk();
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.id', $otherLog->id);
        $res->assertJsonPath('data.0.user_id', $otherActor->id);
    }

    public function test_show_returns_a_single_audit_log_with_its_values(): void
    {
        $admin = $this->superAdmin();
        $log = $this->auditLog($admin, 'payments', 'updated', [
            'record_id' => 42,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'paid'],
        ]);

        Sanctum::actingAs($admin);

        $res = $this->getJson("/api/audit-logs/{$log->id}");

        $res->assertOk();
        $res->assertJsonPath('data.id', $log->id);
        $res->assertJsonPath('data.record_id', 42);
        $res->assertJsonPath('data.table_name', 'payments');
        $res->assertJsonPath('data.old_values.status', 'pending');
        $res->assertJsonPath('data.new_values.status', 'paid');
    }

    public function test_customer_without_permission_is_forbidden(): void
    {
        $admin = $this->superAdmin();
        $log = $this->auditLog($admin, 'orders', 'created');

        $customer = User::factory()->create(['is_active' => true]);
        $customer->assignRole('customer_personal');
        Sanctum::actingAs($customer);

        $this->getJson('/api/audit-logs')->assertStatus(403);
        $this->getJson("/api/audit-logs/{$log->id}")->assertStatus(403);
    }

    public function test_guest_is_unauthenticated(): void
    {
        $this->getJson('/api/audit-logs')->assertStatus(401);
    }
}