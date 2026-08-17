<?php

namespace App\Actions\Category;

use App\Models\Category;

class ApproveCategory
{
    public function execute(Category $category): Category
    {
        $category->update([
            'status' => 'approved',
            'is_active' => true,
            'moderation_reason' => null,
        ]);

        return $category->refresh();
    }
}
