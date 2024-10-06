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
        $donations = $request->user()->donations;
        $donations->load('donor', 'donee', 'product');
        $donations->load('product.donationProductMedia');
        $donations = $donations->toArray();

        foreach ($donations as $donation_key => $donation) {
            if ($donation['donor']['photo_profile']) {
                $donations[$donation_key]['donor']['photo_profile'] = $this->getFileUploadedResize(public_path($this->photoProfilePath($donation['donor']['id'])) . '/' . $donation['donor']['photo_profile'], 50);
            }
    
            if ($donation['donee']['photo_profile']) {
                $donations[$donation_key]['donee']['photo_profile'] = $this->getFileUploadedResize(public_path($this->photoProfilePath($donation['donee']['id'])) . '/' . $donation['donee']['photo_profile'], 50);
            }
    
            if ($donation['product']['donation_product_media']) {
                foreach ($donation['product']['donation_product_media'] as $media_key => $media) {
                    if ($media['url']) {
                        $donations[$donation_key]['product']['donation_product_media'][$media_key]['url'] = $this->getFileUploadedResize(public_path($this->mediaProductPath($donation['product']['user_id'])) . '/' . $media['url'], 200);
                    }
                }
            }
        }

        return $donations;
    }

    public function detail(Request $request, $id)
    {
        $donation = $request->user()->donations->find($id);
        $donation->load('donor', 'donee', 'product');
        $donation->load('product.donationProductMedia');

        if ($donation['donor']['photo_profile']) {
            $donation['donor']['photo_profile'] = $this->getFileUploadedResize(public_path($this->photoProfilePath($donation['donor']['id'])) . '/' . $donation['donor']['photo_profile'], 50);
        }

        if ($donation['donee']['photo_profile']) {
            $donation['donee']['photo_profile'] = $this->getFileUploadedResize(public_path($this->photoProfilePath($donation['donee']['id'])) . '/' . $donation['donee']['photo_profile'], 50);
        }

        if ($donation['product']['donation_product_media']) {
            foreach ($donation['product']['donation_product_media'] as $media_key => $media) {
                if ($media['url']) {
                    $donation['product']['donation_product_media'][$media_key]['url'] = $this->getFileUploadedResize(public_path($this->mediaProductPath($donation['product']['user_id'])) . '/' . $media['url'], 200);
                }
            }
        }

        return $donation;
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
            
            $donation = $data->claim($request->amount);
            $donation->load('product');

            DB::commit();

            return $donation;
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

    private function photoProfilePath($id)
    {
        return 'uploads/'.$id.'/profile';
    }

    private function mediaProductPath($id)
    {
        return 'uploads/'.$id.'/donation/product';
    }
}
