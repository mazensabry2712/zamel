<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
            'status' => $this->status,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'moderation_reason' => $this->moderation_reason,
        ];
    }
}
