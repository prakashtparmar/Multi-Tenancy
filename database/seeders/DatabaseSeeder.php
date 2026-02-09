<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CentralAdminSeeder::class);
        $this->call(CentralSeeder::class);

        // Enterprise Seeders
        // Enterprise Seeders
        $this->call([
            TaxSeeder::class, // Added Tax Classes
            WarehouseSeeder::class,
            CategorySeeder::class,
            DemoAgricultureSeeder::class, // Added Demo Data
            VillageSeeder::class,
        ]);
    }
}
