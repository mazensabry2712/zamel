<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class RejectCategory
{
    public function execute(
        Category $category,
        string $reason
    ): Category {
        if ($category->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => [
                    'Only pending categories can be rejected.',
                ],
            ]);
        }

        $category->update([
            'status' => 'rejected',
            'is_active' => false,
            'moderation_reason' => $reason,
        ]);

        return $category->refresh();
    }
}
