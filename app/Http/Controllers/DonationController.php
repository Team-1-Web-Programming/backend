<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->user()->donations;
        return $data;
    }

    public function detail(Request $request, $id)
    {
        $data = $request->user()->donations->find($id);
        return $data;
    }

    public function add(Request $request)
    {
        $data = $request->user()->donations()->create($request->validated());
        return $data;
    }

    public function claim(Request $request, $donation_product_id)
    {
        $data = $request->user()->donations()->where('donation_product_id', $donation_product_id)->first();
        $data->claim();
        return $data;
    }

    public function confirm(Request $request, $id)
    {
        $data = $request->user()->donations->find($id);
        $data->confirm();
        return $data;
    }
}
