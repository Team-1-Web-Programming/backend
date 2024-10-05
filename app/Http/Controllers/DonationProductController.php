<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationProductController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->user()->donation_products;
        return $data;
    }

    public function detail(Request $request, $id)
    {
        $data = $request->user()->donation_products->find($id);
        return $data;
    }

    public function add(Request $request)
    {
        $data = $request->user()->donation_products()->create($request->validated());
        return $data;
    }

    public function edit(Request $request, $id)
    {
        $data = $request->user()->donation_products->find($id);
        $data->update($request->validated());
        return $data;
    }

    public function delete(Request $request, $id)
    {
        $data = $request->user()->donation_products->find($id);
        $data->delete();
        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
