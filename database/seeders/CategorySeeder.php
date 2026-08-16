<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Books',
                'description' => 'University and school books.',
                'seo_title' => 'Student Books Marketplace Egypt',
                'seo_description' => 'Buy and sell university and school books in Egypt.',
            ],
            [
                'name' => 'Notes',
                'description' => 'Notes, summaries, and study materials.',
                'seo_title' => 'Student Notes and Study Materials Egypt',
                'seo_description' => 'Find and exchange useful student notes and study materials.',
            ],
            [
                'name' => 'Electronics',
                'description' => 'Laptops, calculators, tablets, and accessories.',
                'seo_title' => 'Student Electronics Marketplace Egypt',
                'seo_description' => 'Buy and sell electronics and accessories for students.',
            ],
            [
                'name' => 'Lab Equipment',
                'description' => 'Tools and equipment used for study and labs.',
                'seo_title' => 'Student Lab Equipment Egypt',
                'seo_description' => 'Buy and sell laboratory tools and equipment for students.',
            ],
            [
                'name' => 'Other',
                'description' => 'Other student-related items.',
                'seo_title' => 'Other Student Items Egypt',
                'seo_description' => 'Find other useful products and items for students.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'description' => $category['description'],
                    'seo_title' => $category['seo_title'],
                    'seo_description' => $category['seo_description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
