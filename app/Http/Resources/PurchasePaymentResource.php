<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePaymentResource extends JsonResource
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
            'purchase_request_id' => $this->purchase_request_id,
            'purchase_request' => new PurchaseRequestResource($this->whenLoaded('purchaseRequest')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'paid_by' => $this->paid_by,
            'payer' => new UserResource($this->whenLoaded('payer')),
            'amount' => $this->amount,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}