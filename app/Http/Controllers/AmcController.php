<?php

namespace App\Http\Controllers;

use App\Models\Amc;
use App\Models\Scheme;
use Config;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Log;
use Illuminate\Support\Facades\Http;

class AmcController extends Controller
{
    //
    public function index()
    {
        
        return Inertia::render("amc", [
            "amcs"=> Amc::all(),
        ]);
    }
}
