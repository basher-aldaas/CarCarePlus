<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
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
            'branch' => $this->whenLoaded('branch', fn () => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'name_ar' => $this->branch->name_ar,
            ]),
            'destination_branch_id' => $this->destination_branch_id,
            'destination_branch' => $this->whenLoaded('destinationBranch', fn () => $this->destinationBranch ? [
                'id' => $this->destinationBranch->id,
                'name' => $this->destinationBranch->name,
                'name_ar' => $this->destinationBranch->name_ar,
            ] : null),
            'material_id' => $this->material_id,
            'material' => new MaterialResource($this->whenLoaded('material')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'type' => $this->type,
            'quantity' => $this->quantity,
            'quantity_before' => $this->quantity_before,
            'quantity_after' => $this->quantity_after,
            'reference_id' => $this->reference_id,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
