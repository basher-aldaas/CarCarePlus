<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'from_branch_id' => $this->from_branch_id,
            'from_branch' => new BranchResource($this->whenLoaded('fromBranch')),
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'request_type' => $this->request_type,
            'rejection_reason' => $this->rejection_reason,
            'approved_at' => $this->approved_at,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'payment' => $this->whenLoaded('payment', fn () => [
                'id' => $this->payment?->id,
                'amount' => $this->payment?->amount,
                'paid_by' => $this->payment?->paid_by,
                'note' => $this->payment?->note,
                'created_at' => $this->payment?->created_at,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}