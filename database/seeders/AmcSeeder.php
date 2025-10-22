<?php

namespace Database\Seeders;

use App\Models\Amc;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AmcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
        {
            $csvFile = fopen(base_path('database/data/amc.csv'), 'r');
            $firstline = true; // To skip the header row

            while (($data = fgetcsv($csvFile, 2000, ',')) !== false) {
                if (!$firstline) {
                    Amc::create([
                        'id' => $data[2], // Map CSV columns to database columns
                        'name' => $data[3],
                    ]);
                }
                $firstline = false;
            }

            fclose($csvFile);
        }
}
