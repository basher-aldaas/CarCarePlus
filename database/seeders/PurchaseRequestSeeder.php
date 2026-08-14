<?php

namespace Database\Seeders;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        $branches = DB::table('branches')->orderBy('id')->get(['id']);
        $materials = DB::table('materials')->orderBy('id')->get(['id', 'unit_price']);

        if ($branches->count() < 1 || $materials->isEmpty()) {
            return;
        }

        $firstBranch = $branches->first()->id;
        $secondBranch = $branches->count() > 1 ? $branches->get(1)->id : $firstBranch;

        $requests = [
            // 0 => purchase request (external supplier). from_branch_id is null.
            [
                'branch_id'        => $firstBranch,
                'from_branch_id'   => null,
                'status'           => PurchaseRequestStatus::PENDING->value,
                'request_type'     => 0, // purchase
                'notes'            => 'طلب شراء مواد جديدة من المورد',
                'rejection_reason' => null,
                'approved_at'      => null,
                'items'            => [
                    ['material_id' => $materials[0]->id, 'quantity' => 10],
                    ['material_id' => $materials->count() > 1 ? $materials[1]->id : $materials[0]->id, 'quantity' => 20],
                ],
            ],
            // 1 => transfer request between branches. from_branch_id = source branch.
            [
                'branch_id'        => $firstBranch,   // الفرع الطالب / المستلم
                'from_branch_id'   => $secondBranch,  // الفرع المُرسِل / المصدر
                'status'           => PurchaseRequestStatus::APPROVED->value,
                'request_type'     => 1, // transfer
                'notes'            => 'طلب نقل مخزون من فرع آخر',
                'rejection_reason' => null,
                'approved_at'      => now(),
                'items'            => [
                    ['material_id' => $materials[0]->id, 'quantity' => 5],
                ],
            ],
            // 2 => rejected purchase request.
            [
                'branch_id'        => $secondBranch,
                'from_branch_id'   => null,
                'status'           => PurchaseRequestStatus::REJECTED->value,
                'request_type'     => 0,
                'notes'            => 'طلب شراء مرفوض',
                'rejection_reason' => 'تجاوز الميزانية المخصصة',
                'approved_at'      => null,
                'items'            => [
                    ['material_id' => $materials->count() > 1 ? $materials[1]->id : $materials[0]->id, 'quantity' => 100],
                ],
            ],
        ];

        foreach ($requests as $data) {
            $items = $data['items'];
            unset($data['items']);

            // Build items with prices and compute the request total.
            $preparedItems = [];
            $total = 0;

            foreach ($items as $item) {
                $material = $materials->firstWhere('id', $item['material_id']);
                $unitPrice = (float) ($material->unit_price ?? 0);
                $lineTotal = $unitPrice * $item['quantity'];
                $total += $lineTotal;

                $preparedItems[] = [
                    'material_id' => $item['material_id'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $requestId = DB::table('purchase_requests')->insertGetId([
                'branch_id'        => $data['branch_id'],
                'from_branch_id'   => $data['from_branch_id'],
                'status'           => $data['status'],
                'total_amount'     => $total,
                'notes'            => $data['notes'],
                'request_type'     => $data['request_type'],
                'rejection_reason' => $data['rejection_reason'],
                'approved_at'      => $data['approved_at'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($preparedItems as $line) {
                DB::table('purchase_request_items')->insert([
                    'purchase_req_id' => $requestId,
                    'material_id'     => $line['material_id'],
                    'quantity'        => $line['quantity'],
                    'unit_price'      => $line['unit_price'],
                    'total_price'     => $line['total_price'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }
}