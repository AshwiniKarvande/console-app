<?php

namespace Database\Seeders;

use App\Models\Amc;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NavHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //https://www.amfiindia.com/api/average-aum-schemewise?strType=Categorywise&fyId=1&periodId=7467&MF_ID=22
        $periodId = $this->getRecentPeriodId();
        
        foreach (Amc::all() as $amc) {
            $amc->id;
        }

    }

    private function getRecentPeriodId(): int
    {
        $fyId = 1;
        $response = Http::get(env("AMFI_BASE_URL") . "/average-aum-schemewise", 
        ['strType' => 'Categorywise', 'fyId' => $fyId, 'MF_ID' => 64]); // Any AMC id should be fine
        $jsonData = $response->json();
        if ($jsonData["data"]["periods"]) {
            $recentPeriod = $jsonData["data"]["periods"][0];
            Log::info("Recent period: ". $recentPeriod);
            return $recentPeriod["id"];
        } else {
            $fyId = $fyId + 1;
            $response = Http::get(env("AMFI_BASE_URL") . "/average-aum-schemewise", 
        ['strType' => 'Categorywise', 'fyId' => $fyId, 'MF_ID' => 64]); // Any AMC id should be fine
            $jsonData = $response->json();
            $recentPeriod = $jsonData["data"]["periods"][0];
            Log::info("Recent period: ". $recentPeriod);
            return $recentPeriod["id"];
        }
    }

    private function populateNavHistory(int $mfId, int $periodId): void
    {
        Log::info("Populating schemes nav for AMC: ". $mfId ." Scheme: " . $periodId);
        $fyId = 1;
        $response = Http::get(env("AMFI_BASE_URL") . "/average-aum-schemewise", 
            [
                'strType' => 'Categorywise',
                'fyId' => 1, // Any value can do as periodId query parameter takes preference
                'periodId'=> $periodId,
                'MF_ID' => $mfId
            ]
        ); 
        $jsonData = $response->json();
        
        
    }

    private function populateSchemes(int $mfId): void
    {
        Log::info("Populating schemes for AMC: ". $mfId ."");
        $response = Http::get(env("AMFI_BASE_URL") . "/get-nav-history/navs", ['mf_id' => $mfId]);
        
        $jsonData = $response->json();
        if ($jsonData["meta"]["total"] == 0) {
            Log::info("No schemes found for AMC: ". $mfId ."");
            return;
        }
        $schemes = [];
        foreach ($jsonData["data"] as $schemeJson) {
            $schemes[] = [
                "id"=> $schemeJson["nav_id"],
                "name"=> $schemeJson["nav_name"],
                "amc_id"=> $schemeJson["MF_ID"],
            ];
        }
        DB::table('schemes')->insertOrIgnore( $schemes );
        Log::info('Added scheme count: ' . count($schemes));
        print('Added schemes: '. count($schemes) . 'for Amc'. $mfId . '\n');
    }
}
