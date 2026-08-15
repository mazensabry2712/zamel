<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            // 'university_id' => $this->university_id,
            // 'faculty_id' => $this->faculty_id,
            // 'academic_year_id' => $this->academic_year_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
