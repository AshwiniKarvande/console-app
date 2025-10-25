<?php

namespace App\Http\Controllers;

use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Log;

class FundController extends Controller
{
    //
     public function populateFunds()
    {
        $amfiBaseUrl = env("AMFI_BASE_URL");
        
        $response = Http::get($amfiBaseUrl, ['mf_id' => '64']);
        dd($response);

    }
}
