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

        // Any content change requires a fresh moderation review.
        $listing->moderation_status = 'pending';
        $listing->moderation_reason = null;
        $listing->moderated_at = null;

        // A published listing must not remain publicly visible while being re-reviewed.
        if ($listing->status === 'published') {
            $listing->status = 'draft';
            $listing->published_at = null;
        }

        $listing->save();

        return $listing->refresh();
    }
}
