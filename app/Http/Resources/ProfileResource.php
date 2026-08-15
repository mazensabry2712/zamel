<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'education_type' => $this->education_type,
            'university_id' => $this->university_id,
            'faculty_id' => $this->faculty_id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            // Add other fields as needed
        ];
    }
}
