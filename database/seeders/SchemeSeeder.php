<?php

namespace Database\Seeders;

use App\Models\Amc;
use App\Models\Scheme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SchemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Amc::all()->each(function (Amc $amc) {
            $this->populateSchemes($amc);
        });
    }

    private function populateSchemes(Amc $amc): void
    {
        Log::info("Populating schemes for  AMC: " . $amc->id . " = " . $amc->name);
        print ("Populating schemes for  AMC: " . $amc->id . " = " . $amc->name . PHP_EOL);

        $mfId = $amc->id;
        $response = Http::get(env("AMFI_BASE_URL") . "/get-nav-history/navs", ['mf_id' => $mfId]);

        $jsonData = $response->json();
        if ($jsonData["meta"]["total"] == 0) {
            Log::info("No schemes found for AMC: " . $mfId . "");
            print ("No schemes found " . PHP_EOL);
            return;
        }
        $schemes = [];
        foreach ($jsonData["data"] as $schemeJson) {
            $schemes[] = [
                "id" => $schemeJson["nav_id"],
                "name" => $schemeJson["nav_name"],
                "amc_id" => $schemeJson["MF_ID"],
            ];
        }
        DB::table('schemes')->insertOrIgnore($schemes);
        Log::info('Added scheme count: ' . count($schemes));
        print ('  Added scheme count: ' . count($schemes) . PHP_EOL);
    }
}
