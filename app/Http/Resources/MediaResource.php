<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getUrl(),
            'thumb_url' => $this->getUrl('thumb'),
            'medium_url' => $this->getUrl('medium'),
            'order' => $this->order_column,
            'created_at' => $this->created_at,
        ];
    }
}
