<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DonationCategoryController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->user()->donation_categories;
        return $data;
    }

    public function add(Request $request)
    {
        $data = $request->user()->donation_categories()->create($request->validated());
        return $data;
    }

    public function edit(Request $request, $id)
    {
        $data = $request->user()->donation_categories->find($id);
        $data->update($request->validated());
        return $data;
    }

    public function delete(Request $request, $id)
    {
        $data = $request->user()->donation_categories->find($id);
        $data->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
