<?php

namespace Database\Seeders;

use App\Models\Amc;
use App\Models\Fund;
use App\Models\Scheme;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;

class FundAumHistorySeeder extends Seeder
{
    private const AUM_KEY = "ExcludingFundOfFundsDomesticButIncludingFundOfFundsOverseas";

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //https://www.amfiindia.com/api/average-aum-schemewise?strType=Categorywise&fyId=4&periodId=19&MF_ID=64
        /*
        1. URL https://www.amfiindia.com/api/average-aum-schemewise?strType=Categorywise&fyId=1&MF_ID=64
        2. Get all years
        3. Get all period for all years
        4. Get the data for all periods and update
        */
        Amc::all()->each(function (Amc $amc) {
            Log::info("Processing AMC: " . $amc->id ." = ". $amc->name);
            print("Processing AMC: " . $amc->id ." = ". $amc->name. PHP_EOL);

            $amcId = $amc->id;
            $map = $this->fundCategoriesWithLargeAum($amcId);
            $categoriesWithLargeAum = $map['categories'];
            if ($categoriesWithLargeAum) {
                $this->populateFundAumHistory($amcId, $map['yearIds'], $categoriesWithLargeAum);
            }
        });
    }

    private function fundCategoriesWithLargeAum(int $amcId): array
    {
        $aumInLac = env("FUND_MIN_AUM_CR") * 100.0;
        if ($aumInLac == 0) {
            $aumInLac = 100000;
        }
        $firstRequestQueryParams = [
            "strType" => "Categorywise",
            "fyId" => "1",
            "MF_ID" => $amcId,
        ];
        $jsonData = $this->getAumJsonResponse($firstRequestQueryParams);

        $yearIds = [];
        $categoriesWithLargeAum = [];
        foreach ($jsonData["years"] as $year) {
            $yearIds[] = $year["id"];
        }
        if ($jsonData["data"]["periods"]) {
            $queryParams = [
                "strType" => "Categorywise",
                "fyId" => "1",
                "periodId" => $jsonData["data"]["periods"][0]["id"],
                "MF_ID" => $amcId,
            ];
            $jsonData = $this->getAumJsonResponse($queryParams);
            foreach ($jsonData["data"] as $fundData) {
                if (
                    $fundData["schemes"] &&
                    $fundData["totalAUM"][self::AUM_KEY] >= $aumInLac
                ) {
                    // At least 1 scheme (to ignore AMC total)
                    // and Aum in lac, process funds having aum = 1000 cr
                    $categoriesWithLargeAum[] = $fundData["SchemeCat_Desc"];
                }
            }
        }
        return ['categories' => $categoriesWithLargeAum, 'yearIds' => $yearIds];
    }

    private function populateFundAumHistory(int $amcId, array $yearIds, array $categoriesWithLargeAum): void
    {
        $periodIds = $this->getPeriodIds($amcId, $yearIds);
        print(' Found periodIds: '. count($periodIds) .PHP_EOL);
        $chunks = array_chunk($periodIds, 10); // Chunk to avoid OOM error
        foreach ($chunks as $chunk) {
            $this->processPeriodChunk($amcId, $chunks, $categoriesWithLargeAum);
        }
    }

    private function processPeriodChunk(int $amcId, array $periodIds, array $categoriesWithLargeAum) {
        $responses = $this->getAumResponseForPeriods($amcId, $periodIds);

        $fundIdByCategory = $this->getFundIdByCategory($amcId);
        
        $fundAumHistory = [];
        $schemeAumHistory = [];
        $schemeFundMap = []; // To assign fund_id to scheme
        foreach ($responses as $response) {
            $jsonData = $response->json();
            foreach ($jsonData["data"] as $obj) {
                $category = $obj["SchemeCat_Desc"];
                $totalAum = $obj["totalAUM"][self::AUM_KEY];
                if ($totalAum > 0 && in_array($category, $categoriesWithLargeAum)) {
                    //process to store
                    $date = $this->formatDate($obj["strdtAUM"]);
                    $fundId = $fundIdByCategory[$category] ?? null;

                    if ($fundId) {
                        $fundAumHistory[] = [
                            "start_date" => $date,
                            "total_aum" => $totalAum,
                            "fund_id" => $fundId,
                        ];
                    } else {
                        print("No fund found for category: ". $category . PHP_EOL);
                    }
                    
                    foreach ($obj["schemes"] as $scheme) {
                        $schemeId = $scheme["AMFI_Code"];
                        $schemeAumHistory[] = [
                            "start_date" => $date,
                            "scheme_id" => $schemeId,
                            "aum" => $scheme["AverageAumForTheMonth"][self::AUM_KEY],
                        ];
                        $schemeFundMap[$schemeId] = $fundId;
                    }
                }
            }
        }
        $this->updateSchemeForFundId($schemeFundMap);
        print ("Fund aum history count: " . count($fundAumHistory) . PHP_EOL);
        print ("Scheme aum history: " . count($schemeAumHistory) . PHP_EOL);
        DB::table('fund_aum_histories')->insertOrIgnore($fundAumHistory);
        DB::table('scheme_aum_histories')->insertOrIgnore($schemeAumHistory);
    }

    private function updateSchemeForFundId($schemeFundMap)
    {
        foreach ($schemeFundMap as $schemeId => $fundId) {
            $scheme = Scheme::find($schemeId);
            $scheme->fund_id = $fundId;
            $scheme->save();
        }
    }

    private function getPeriodIds(int $amcId, array $yearIds): array
    {
        $periodIds = [];
        $responses = Http::pool(function (Pool $pool) use ($yearIds, $amcId) {
            $requests = [];
            foreach ($yearIds as $yearId) {
                $queryParams = [
                    "strType" => "Categorywise",
                    "fyId" => $yearId,
                    "MF_ID" => $amcId,
                ];
                $requests[] = $pool->get(env("AMFI_BASE_URL") . "/average-aum-schemewise", $queryParams);
            }
            return $requests;
        });
        foreach ($responses as $response) {
            $jsonData = $response->json();
            $periods = $jsonData["data"]["periods"];
            foreach ($periods as $period) {
                $periodIds[] = $period["id"];
            }
        }
        return $periodIds;
    }

    private function getAumJsonResponse(array $queryParams)
    {
        $amfiBaseUrl = env("AMFI_BASE_URL");
        $response = Http::get($amfiBaseUrl . "/average-aum-schemewise", $queryParams);
        return $response->json();
    }

    private function getAumResponseForPeriods(int $amcId, array $periodIds)
    {
        return Http::pool(function (Pool $pool) use ($amcId, $periodIds) {
            $requests = [];
            foreach ($periodIds as $periodId) {
                $queryParams = [
                    "strType" => "Categorywise",
                    "fyId" => 1,
                    "periodId" => $periodId,
                    "MF_ID" => $amcId,
                ];
                $requests[] = $pool->get(env("AMFI_BASE_URL") . "/average-aum-schemewise", $queryParams);
            }
            return $requests;
        });
    }

    private function getFundIdByCategory(int $amcId): array
    {
        return Fund::where("amc_id", $amcId)
            ->pluck("id", "category")
            ->toArray();
    }

    /**
     * Format date from dd-MMM-yyy to yyyy-mm-dd
     * @param string $date in dd-MMM-yyyy format
     * @return string date in yyyy-mm-dd format
     */
    private function formatDate(string $date): string
    {
        return Carbon::parse($date)->format("Y-m-d");
    }
}
