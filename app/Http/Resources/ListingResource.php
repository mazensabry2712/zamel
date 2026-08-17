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
<<<<<<< HEAD

            'status' => $this->status,

=======
            'status' => $this->status,
>>>>>>> 727ce3476766672efa698dc1fb932329ee4f553b
            'moderation' => [
                'status' => $this->moderation_status,
                'reason' => $this->moderation_reason,
                'moderated_at' => $this->moderated_at,
            ],
<<<<<<< HEAD

=======
>>>>>>> 727ce3476766672efa698dc1fb932329ee4f553b
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ],
<<<<<<< HEAD

=======
>>>>>>> 727ce3476766672efa698dc1fb932329ee4f553b
            'created_at' => $this->created_at,
        ];
    }
}
