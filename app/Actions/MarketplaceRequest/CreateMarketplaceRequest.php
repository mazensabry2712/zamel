<?php

namespace App\Actions\MarketplaceRequest;

use App\Models\Category;
use App\Models\MarketplaceRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateMarketplaceRequest
{
    public function execute(User $user, array $data): MarketplaceRequest
    {
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

        return MarketplaceRequest::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'budget' => $data['budget'] ?? null,
            'status' => 'open',
            'expires_at' => $data['expires_at'] ?? null,
        ]);
    }
}
