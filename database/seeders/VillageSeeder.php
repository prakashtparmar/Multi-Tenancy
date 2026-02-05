<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/villages.csv');

        $file = fopen($path, 'r');
        fgetcsv($file); // skip header

        $data = [];
        $chunkSize = 1000;

        while (($row = fgetcsv($file)) !== false) {

            $data[] = [
                'village_name'  => $row[0],
                'pincode'       => $row[1],
                'post_so_name'  => $this->clean($row[2]),
                'taluka_name'   => $this->clean($row[3]),
                'district_name' => $this->clean($row[4]),
                'state_name'    => $this->clean($row[5]),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            if (count($data) >= $chunkSize) {
                DB::table('villages')->insert($data);
                $data = [];
            }
        }

        if (!empty($data)) {
            DB::table('villages')->insert($data);
        }

        fclose($file);
    }

    private function clean($value)
    {
        return ($value === '#N/A' || $value === '') ? null : trim($value);
    }
}
