<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressAddRequest;
use App\Http\Requests\AddressEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->user()->addresses;
        return $data;
    }

    public function detail(Request $request, $id)
    {
        $data = $request->user()->addresses->find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Address not found',
            ])->setStatusCode(404);
        }
        return $data;
    }

    public function default(Request $request)
    {
        $data = $request->user()->addresses->where('is_default', 1)->first();
        if (!$data) {
            return response()->json([
                'message' => 'Default address not found',
            ])->setStatusCode(404);
        }
        return $data;
    }

    public function add(AddressAddRequest $request)
    {
        DB::beginTransaction();

        try {
            $is_default = $request->is_default ? 1 : 0;
            if ($is_default) {
                $request->user()->addresses()->where('is_default', 1)->update(['is_default' => 0]);
            }
            $data = $request->user()->addresses()->create($request->all());
    
            DB::commit();

            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function edit(AddressEditRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $is_default = $request->is_default ? 1 : 0;
            if ($is_default) {
                $request->user()->addresses()->where('is_default', 1)->update(['is_default' => 0]);
            }
            $data = $request->user()->addresses->find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Address not found',
                ])->setStatusCode(404);
            }
            
            $data->update($request->all());

            DB::commit();

            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $data = $request->user()->addresses->find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Address not found',
                ])->setStatusCode(404);
            }
            $data->delete();
            DB::commit();

            return response()->json([
                'message' => 'Address deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
    }
}
