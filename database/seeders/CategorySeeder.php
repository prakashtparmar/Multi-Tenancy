<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Seeds & Saplings',
            'Fertilizers',
            'Farming Tools',
            'Pesticides',
            'Irrigation Equipment',
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat, 'slug' => \Str::slug($cat), 'is_active' => true]);
        }
    }
}
