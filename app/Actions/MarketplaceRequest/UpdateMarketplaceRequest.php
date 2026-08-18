<?php

namespace App\Actions\MarketplaceRequest;

use App\Models\Category;
use App\Models\MarketplaceRequest;
use Illuminate\Validation\ValidationException;

class UpdateMarketplaceRequest
{
    public function execute(MarketplaceRequest $marketplaceRequest, array $data): MarketplaceRequest
    {
        if (array_key_exists('category_id', $data)) {
            $category = Category::query()
                ->whereKey($data['category_id'])
                ->where('status', 'approved')
                ->where('is_active', true)
                ->exists();

            if (! $category) {
                throw ValidationException::withMessages([
                    'category_id' => [
                        'The selected category is not available.',
                    ],
                ]);
            }
        }

        $marketplaceRequest->update($data);

        return $marketplaceRequest->refresh();
    }
}
