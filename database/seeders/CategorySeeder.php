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
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets including smartphones, laptops, tablets, and accessories.',
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashion and apparel including shirts, pants, dresses, and accessories.',
            ],
            [
                'name' => 'Home & Kitchen',
                'slug' => 'home-kitchen',
                'description' => 'Home essentials and kitchen appliances for your household needs.',
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear for fitness and adventure enthusiasts.',
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Books across various genres including fiction, non-fiction, and educational materials.',
            ],
            [
                'name' => 'Toys & Games',
                'slug' => 'toys-games',
                'description' => 'Toys, games, and entertainment products for all ages.',
            ],
            [
                'name' => 'Health & Beauty',
                'slug' => 'health-beauty',
                'description' => 'Health and beauty products including skincare, cosmetics, and wellness items.',
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Automotive parts, accessories, and car care products.',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
