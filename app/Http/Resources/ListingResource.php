<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'condition' => $this->condition,
            'status' => $this->status,
            'moderation' => [
                'status' => $this->moderation_status,
                'reason' => $this->moderation_reason,
                'moderated_at' => $this->moderated_at,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
