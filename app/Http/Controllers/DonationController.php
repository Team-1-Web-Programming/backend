<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonationStatusRequest;
use App\Models\Donation;
use App\Models\DonationProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function claim(Request $request, $donation_product_id)
    {
        DB::beginTransaction();

        try {
            $data = DonationProduct::where('id', $donation_product_id)
            ->where('user_id', '<>', $request->user()->id)
            ->first();

            if (!$data) {
                return response()->json([
                    'message' => 'Product not found',
                ])->setStatusCode(404);
            }
            
            $data->claim($request->amount);

            DB::commit();

            return $data;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode($e->getCode() ?? 500);
        }
    }

    public function status(DonationStatusRequest $request, Donation $donation)
    {
        DB::beginTransaction();

        try {
            $status = $request->get('status');
            
            switch ($status) {
                case 'confirmed':
                    $donation->confirmed();
                    break;
                case 'taken':
                    $donation->taken();
                    break;
                case 'canceled':
                    $donation->canceled();
                    break;
                case 'rejected':
                    $donation->rejected($request->get('reason'));
                    break;
            }

            DB::commit();

            return $donation;
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode($e->getCode() ?? 500);
        }
    }
}
