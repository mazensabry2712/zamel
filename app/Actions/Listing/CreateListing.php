<?php

namespace App\Actions\Listing;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateListing
{
    public function execute(
        User $user,
        array $data
    ): Listing {
        $category = Category::query()
            ->whereKey($data['category_id'])
            ->where('status', 'approved')
            ->where('is_active', true)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => [
                    'The selected category is not available.',
                ],
            ]);
        }

        return Listing::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'condition' => $data['condition'],
            'status' => 'draft',
            'moderation_status' => 'pending',
            'moderation_reason' => null,
            'moderated_at' => null,
            'published_at' => null,
        ]);
    }
}
