<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

class ApproveCategory
{
    public function execute(Category $category): Category
    {
        if ($category->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => [
                    'Only pending categories can be approved.',
                ],
            ]);
        }

        $category->update([
            'status' => 'approved',
            'is_active' => true,
            'moderation_reason' => null,
        ]);

        return $category->refresh();
    }
}
