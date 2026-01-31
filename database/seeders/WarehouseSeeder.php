<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Warehouse::count() === 0) {
            Warehouse::create([
                'name' => 'Main Warehouse',
                'code' => 'WH-MAIN',
                'address' => '123 Main St, Central City',
                'is_default' => true,
                'is_active' => true,
            ]);
        }
    }
}
