<?php

namespace Database\Seeders;

use App\Models\Fund;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Amc;
use App\Models\Scheme;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class FundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info("Env AMFI_BASE_URL= " . env("AMFI_BASE_URL"));
        $amfiBaseUrl = env("AMFI_BASE_URL");
        
        $response = Http::get($amfiBaseUrl . "/scheme-details");
        
        $jsonData = $response->json();
        $openEndedFunds = [];
        foreach ($jsonData["data"] as $fundJson) {
            if ($fundJson["SchemeType_Desc"] == "Open Ended") {
                $fund = [
                    "id" => $fundJson["id"],
                    "name" => $fundJson["Scheme_Name"],
                    "objective" => $fundJson["Scheme_Objective"],
                    "type" => $fundJson["SchemeType_Desc"],
                    "category" => $fundJson["SchemeCat_Desc"],
                    "min_amt" => $fundJson["Scheme_min_amt"],
                    "entry_exit_load" => $fundJson["Scheme_load"],
                    "launch_date" => $fundJson["Launch_Date"],
                    "amc_id" => $fundJson["MF_Id"]
                ];
                $openEndedFunds[] = $fund;
            }
        }
        Log::info("Open ended fund count: " . count($openEndedFunds));
        DB::table('funds')->insertOrIgnore( $openEndedFunds );
    }
}
