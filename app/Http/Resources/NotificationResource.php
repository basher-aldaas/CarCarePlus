<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type?->value,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at,
            'sent_via' => $this->sent_via,
            'created_at' => $this->created_at,
        ];
    }
}