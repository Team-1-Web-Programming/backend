<?php

namespace App\Http\Controllers;

use App\Models\DonationCategory;
use Illuminate\Http\Request;

class DonationCategoryController extends Controller
{
    public function index(Request $request)
    {
        $data = DonationCategory::generateTree();
        return $data;
    }

    public function add(Request $request)
    {
        $data = DonationCategory::create($request->all());
        return $data;
    }

    public function edit(Request $request, $id)
    {
        $data = DonationCategory::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Category not found',
            ])->setStatusCode(404);
        }
        $data->update($request->all());
        return $data;
    }

    public function delete(Request $request, $id)
    {
        $data = DonationCategory::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Category not found',
            ])->setStatusCode(404);
        }
        $data->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
