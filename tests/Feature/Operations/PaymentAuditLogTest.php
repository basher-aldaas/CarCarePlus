<?php

namespace Tests\Feature\Operations;

use App\Enums\PaymentEnums\PaymentMethod;
use App\Enums\PaymentEnums\PaymentStatus;
use App\Enums\PaymentEnums\PaymentType;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    public function test_creating_a_payment_writes_a_created_audit_row(): void
    {
        $actor = $this->user();
        Sanctum::actingAs($actor);

        $payment = Payment::create([
            'user_id' => $actor->id,
            'payment_number' => 'PN-' . uniqid(),
            'type' => PaymentType::WALLET_TOPUP,
            'method' => PaymentMethod::WALLET,
            'status' => PaymentStatus::PAID,
            'amount' => 120.00,
        ]);

        $log = AuditLog::where('table_name', 'payments')
            ->where('record_id', $payment->id)
            ->where('action', 'created')
            ->firstOrFail();

        $this->assertEquals($actor->id, $log->user_id);
        $this->assertNull($log->old_values);
        $this->assertEquals(PaymentMethod::WALLET->value, $log->new_values['method']);
        $this->assertEquals(120.0, (float) $log->new_values['amount']);
        // Surrogate/timestamp columns are stripped from the snapshot.
        $this->assertArrayNotHasKey('id', $log->new_values);
        $this->assertArrayNotHasKey('created_at', $log->new_values);
    }

    public function test_updating_payment_status_writes_an_updated_audit_row_with_diff(): void
    {
        $actor = $this->user();
        Sanctum::actingAs($actor);

        $payment = Payment::create([
            'user_id' => $actor->id,
            'payment_number' => 'PN-' . uniqid(),
            'type' => PaymentType::ORDER,
            'method' => PaymentMethod::CASH,
            'status' => PaymentStatus::PENDING,
            'amount' => 40.00,
        ]);

        $payment->update(['status' => PaymentStatus::REFUNDED]);

        $log = AuditLog::where('record_id', $payment->id)
            ->where('action', 'updated')
            ->firstOrFail();

        $this->assertEquals(PaymentStatus::PENDING->value, $log->old_values['status']);
        $this->assertEquals(PaymentStatus::REFUNDED->value, $log->new_values['status']);
        // A status-only change must not smuggle unrelated columns into the diff.
        $this->assertArrayNotHasKey('amount', $log->new_values);
    }

    public function test_actor_falls_back_to_payment_owner_when_unauthenticated(): void
    {
        $owner = $this->user();

        // No Sanctum::actingAs — simulates a system/queue context.
        $payment = Payment::create([
            'user_id' => $owner->id,
            'payment_number' => 'PN-' . uniqid(),
            'type' => PaymentType::SPARE,
            'method' => PaymentMethod::CASH,
            'status' => PaymentStatus::PENDING,
            'amount' => 30.00,
        ]);

        $log = AuditLog::where('record_id', $payment->id)
            ->where('action', 'created')
            ->firstOrFail();

        $this->assertEquals($owner->id, $log->user_id);
    }

    public function test_every_method_produces_a_created_audit_row(): void
    {
        $actor = $this->user();
        Sanctum::actingAs($actor);

        $methods = [
            PaymentMethod::WALLET,
            PaymentMethod::CASH,
            PaymentMethod::POINT,
            PaymentMethod::PACKAGE,
        ];

        foreach ($methods as $method) {
            $payment = Payment::create([
                'user_id' => $actor->id,
                'payment_number' => 'PN-' . uniqid(),
                'type' => PaymentType::ORDER,
                'method' => $method,
                'status' => PaymentStatus::PAID,
                'amount' => 10.00,
            ]);

            $this->assertDatabaseHas('audit_logs', [
                'table_name' => 'payments',
                'record_id' => $payment->id,
                'action' => 'created',
            ]);
        }
    }
}