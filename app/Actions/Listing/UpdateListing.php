<?php

namespace App\Actions\Listing;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Validation\ValidationException;

class UpdateListing
{
    public function execute(Listing $listing, array $data): Listing
    {
        if (array_key_exists('category_id', $data)) {
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
        }

        $listing->fill($data);
        $listing->resetForModerationReview();
        $listing->save();

        return $listing->refresh();
    }
}
