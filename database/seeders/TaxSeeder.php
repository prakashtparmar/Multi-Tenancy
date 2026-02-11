<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxClass;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            ['name' => 'Standard Rate', 'slug' => 'standard-rate', 'rate' => 18.00],
            ['name' => 'Reduced Rate', 'slug' => 'reduced-rate', 'rate' => 12.00],
            ['name' => 'Essential Rate', 'slug' => 'essential-rate', 'rate' => 5.00],
            ['name' => 'Zero Rated', 'slug' => 'zero-rated', 'rate' => 0.00],
            ['name' => 'Exempt', 'slug' => 'exempt', 'rate' => 0.00],
        ];

        foreach ($classes as $class) {
            $taxClass = TaxClass::firstOrCreate(
                ['slug' => $class['slug']],
                ['name' => $class['name']]
            );

            if ($taxClass->wasRecentlyCreated) {
                $taxClass->rates()->create([
                    'name' => 'GST ' . $class['rate'] . '%',
                    'rate' => $class['rate'],
                    'breakdown' => [
                        'cgst' => $class['rate'] / 2,
                        'sgst' => $class['rate'] / 2,
                    ]
                ]);
            }
        }

        // Assign random tax classes to existing products for testing
        $products = \App\Models\Product::all();
        $taxClasses = \App\Models\TaxClass::all();

        if ($taxClasses->count() > 0) {
            foreach ($products as $product) {
                // Skip if already assigned or just overwrite for ensuring test data
                $product->tax_class_id = $taxClasses->random()->id;
                $product->save();
            }
        }
    }
}
