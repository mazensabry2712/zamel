<?php

namespace App\Actions\Category;

use App\Models\Category;

class RejectCategory
{
    public function execute(
        Category $category,
        string $reason
    ): Category {
        $category->update([
            'status' => 'rejected',
            'is_active' => false,
            'moderation_reason' => $reason,
        ]);

        return $category->refresh();
    }
}
