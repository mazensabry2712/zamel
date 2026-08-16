<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacultyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
<<<<<<< HEAD
        // return parent::toArray($request);
=======
>>>>>>> 9b584c399be498740e4073dad7543e71aafd55f6
        return [
            'id' => $this->id,
            'university_id' => $this->university_id,
            'name' => $this->name,
<<<<<<< HEAD
            'slug' => $this->slug,        ];
=======
            'slug' => $this->slug,
        ];
>>>>>>> 9b584c399be498740e4073dad7543e71aafd55f6
    }
}
