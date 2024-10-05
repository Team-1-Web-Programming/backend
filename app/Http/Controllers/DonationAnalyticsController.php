<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->user()->donation_analytics;
        return $data;
    }
}
