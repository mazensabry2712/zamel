<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CreateCategorySuggestion
{
    public function execute(
        User $user,
        array $data
    ): Category {
        return Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'status' => 'pending',
            'created_by' => $user->id,
            'is_active' => false,
        ]);
    }
}
