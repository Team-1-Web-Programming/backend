<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonationProductAddRequest;
use App\Http\Requests\DonationProductEditRequest;
use App\Models\DonationProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationProductController extends Controller
{
    private $upload_path;

    public function __construct(Request $request)
    {
        if ($request->user()) {
            $this->upload_path = 'uploads/'.$request->user()->id.'/donation/product';
        }
    }

    public function index(Request $request)
    {
        $category = $request->get('category');
        $city = $request->get('city');
        $province = $request->get('province');
        $searchTerm = $request->get('search');
        $page = $request->get('page');

        $query = DonationProduct::query();

        $query->join('addresses', 'donation_products.address_id', '=', 'addresses.id');

        $query->join('donation_product_categories', 'donation_products.id', '=', 'donation_product_categories.donation_product_id')
            ->join('donation_categories', 'donation_product_categories.donation_category_id', '=', 'donation_categories.id');

        if ($request->user()) {
            $query->where('donation_products.user_id', '<>', $request->user()->id);
        }

        $query->when($category, function ($query, $category) {
            return $query->where('donation_categories.title', $category);
        });

        if ($city || $province ) {
            $query->when($city, function ($query, $city) {
                return $query->where('addresses.city', $city);
            });
            $query->when($province, function ($query, $province) {
                return $query->where('addresses.province', $province);
            });
        } else if ($request->user() && $address = $request->user()->addresses()->where('is_default', 1)->first()) {
            $query->where('addresses.city', $address->city);
            $query->where('addresses.province', $address->province);
        }

        $query->when($searchTerm, function ($query, $searchTerm) {
            return $query->where(function ($query) use ($searchTerm) {
                $query->where('donation_products.title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('donation_products.description', 'like', '%' . $searchTerm . '%');
            });
        });

        if ($page) {
            $donationProducts = $query->paginate(10, ['donation_products.*'], 'page', $page);
        } else {
            $donationProducts = $query->get('donation_products.*');
        }

        $donationProducts = $donationProducts->load('user', 'address', 'donationCategory', 'donationProductMedia')->toArray();

        foreach ($donationProducts as $donationProducts_key => $product) {
            foreach ($product['donation_product_media'] as $media_key => $media) {
                $donationProducts[$donationProducts_key]['donation_product_media'][$media_key]['url'] = $this->getFileUploadedResize(public_path('uploads/'.$product['user_id'].'/donation/product') . '/' . $media['url'], 200);
            }
        }

        return response()->json($donationProducts);
    }

    public function detail(Request $request, $id)
    {
        $data = DonationProduct::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Product not found',
            ])->setStatusCode(404);
        }
        $data->load('user', 'address', 'donationCategory', 'donationProductMedia');

        foreach ($data->donationProductMedia as $media) {
            $media->url = $this->getFileUploadedResize(public_path('uploads/'.$data->user_id.'/donation/product') . '/' . $media->url, 200);
        }
        
        return $data;
    }

    public function donor_products(Request $request)
    {
        $page = $request->get('page');

        $data = $request->user()->donation_products();

        if ($page) {
            $data = $data->paginate(10, ['*'], 'page', $page);
        }

        $data = $data->get();

        $data = $data->load('address', 'donationCategory', 'donationProductMedia')->toArray();

        foreach ($data as $data_key => $product) {
            foreach ($product['donation_product_media'] as $media_key => $media) {
                $data[$data_key]['donation_product_media'][$media_key]['url'] = $this->getFileUploadedResize(public_path('uploads/'.$product['user_id'].'/donation/product') . '/' . $media['url'], 200);
            }
        }

        return $data;
    }

    public function donor_detail(Request $request, $id)
    {
        $data = $request->user()->donation_products->find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Product not found',
            ])->setStatusCode(404);
        }
        $data->load('address', 'donationCategory', 'donationProductMedia');

        foreach ($data->donationProductMedia as $media) {
            $media->url = $this->getFileUploadedResize(public_path($this->upload_path) . '/' . $media->url, 200);
        }
        
        return $data;
    }

    public function add(DonationProductAddRequest $request)
    {
        DB::beginTransaction();

        $tmp_files = [];

        try {
            $product = $request->user()->donation_products()->create($request->all());
            
            if ($request->has('category_id')) {
                foreach ($request->category_id as $category_id) {
                    $product->donationProductCategory()->create([
                        'user_id' => $request->user()->id,
                        'donation_product_id' => $product->id,
                        'donation_category_id' => $category_id
                    ]);
                }
            }

            if ($request->hasFile('media')) {
                $files = $request->file('media');
                foreach ($files as $key => $file) {
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move($this->upload_path, $filename);

                    $tmp_files[] = $filename;

                    $product->donationProductMedia()->create([
                        'title' => $file->getClientOriginalName(),
                        'type' => 'image',
                        'url' => $filename
                    ]);
                }
            }
            
            $data = $product->load('address', 'donationCategory', 'donationProductMedia');
            
            foreach ($data->donationProductMedia as $media) {
                $media->url = $this->getFileUploadedResize(public_path($this->upload_path) . '/' . $media->url, 200);
            }
            
            DB::commit();
            
            return $data;
        } catch (\Exception $e) {
            DB::rollback();

            # rollback tmp_files
            foreach ($tmp_files as $file) {
                $old_files = public_path($this->upload_path) . '/' . $file;
                if (file_exists($old_files)) {
                    unlink($old_files);
                }
            }

            return response()->json([
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    public function edit(DonationProductEditRequest $request, $id)
    {
        DB::beginTransaction();

        $tmp_files = [];

        try {

            $data = $request->user()->donation_products->find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Product not found',
                ])->setStatusCode(404);
            }
            
            if ($request->hasFile('media')) {
                $old_media = $data->donationProductMedia()->get();

                $data->donationProductMedia()->delete();

                $files = $request->file('media');
                foreach ($files as $key => $file) {
                    $filename = time() . '.' . $file->getClientOriginalExtension();
                    $file->move($this->upload_path, $filename);

                    $tmp_files[] = $filename;

                    $data->donationProductMedia()->create([
                        'title' => $file->getClientOriginalName(),
                        'type' => 'image',
                        'url' => $filename
                    ]);
                }

                if ($old_media) {
                    foreach ($old_media as $media) {
                        $old_files = public_path($this->upload_path) . '/' . $media->url;
                        if (file_exists($old_files)) {
                            unlink($old_files);
                        }
                    }
                }
            }

            if ($request->has('title')) $data->title = $request->title;
            if ($request->has('description')) $data->description = $request->description;
            if ($request->has('amount')) $data->amount = $request->amount;
            if ($request->has('address_id')) $data->address_id = $request->address_id;
            
            if ($request->has('category_id')) {
                $data->donationProductCategory()->delete();
                foreach ($request->category_id as $category_id) {
                    $data->donationProductCategory()->create([
                        'user_id' => $request->user()->id,
                        'donation_product_id' => $data->id,
                        'donation_category_id' => $category_id
                    ]);
                }
            }

            $data->save();

            $data = $data->load('address', 'donationCategory', 'donationProductMedia');
            
            foreach ($data->donationProductMedia as $media) {
                $media->url = $this->getFileUploadedResize(public_path($this->upload_path) . '/' . $media->url, 200);
            }
            
            DB::commit();
            return $data;

        } catch (\Exception $e) {
            DB::rollback();

            # rollback tmp_files
            foreach ($tmp_files as $file) {
                $old_files = public_path($this->upload_path) . '/' . $file;
                if (file_exists($old_files)) {
                    unlink($old_files);
                }
            }

            return response()->json([
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function delete(Request $request, $id)
    {
        DB::beginTransaction();

        $tmp_files = [];

        try {
            $data = $request->user()->donation_products->find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Product not found',
                ])->setStatusCode(404);
            }

            
            
            $data->donationProductCategory()->delete();
            
            $media = $data->donationProductMedia()->get();
            $data->donationProductMedia()->delete();

            foreach ($media as $item) {
                $old_files = public_path($this->upload_path) . '/' . $item->url;
                if (file_exists($old_files)) {
                    unlink($old_files);
                }
            }
            
            $data->delete();

            DB::commit();
            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
