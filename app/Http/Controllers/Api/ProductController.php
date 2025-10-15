<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVideo;
use App\Trait\ApiResponse;
use BaconQrCode\Encoder\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all Products.
     */
    public function index()
    {
        $partner = auth()->user()->id;
        try {
            $products = Product::with(['images', 'videos', 'partner', 'ratings'])->where('partner_id', $partner)->get();
            return $this->success(ProductResource::collection($products), 'Products retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve products: ' . $e->getMessage(), 500);
        }
    }

    public function statusUpdate(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        $action = $request->status;
        $status = $action == 'accept' ? 'active' : 'suspended';

        Product::find($request->id)->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Product {$status} successfully"
        ]);
    }

    /**
     * Fetch a single Product by ID.
     */
    public function show($id)
    {
        try {
            $product = Product::with('images', 'videos', 'partner')->find($id);

            if (!$product) {
                return $this->error('Product not found', 404);
            }

            return $this->success(new ProductResource($product), 'Product retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve product: ' . $e->getMessage(), 500);
        }
    }



    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized or no associated partner'], 401);
        }

        // ✅ Custom validation using Validator
        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'material' => 'required|string|max:255',
            'careMethod' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'basePrice' => 'required|numeric',
            'extensionPrice' => 'required|numeric',
            'deposite' => 'required|numeric',
            'lateFee' => 'required|numeric',
            'replacementValue' => 'required|numeric',
            'keepToBuyPrice' => 'required|numeric',
            'minRentalPeriod' => 'required|string|max:100',
            'maxRentalPeriod' => 'required|string|max:100',
            'prepBuffer' => 'required|string|max:100',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'barcode' => 'required|string',
            'fitType' => 'required|string|max:255',
            'chest' => 'required|string|max:100',
            'lengthType' => 'required|string|max:100',
            'sleeve' => 'required|string|max:100',
            'coditionGrade' => 'nullable',
            'status' => 'required|string|max:100',
            'note' => 'nullable|string|max:1000',
            'unit' => 'required',
            'images.*' => 'required|image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'video_url' => 'nullable|url|max:1000',
        ]);

        // ✅ Return validation errors in JSON format
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $product = Product::create([
                'partner_id' => $user->id,
                'name' => $validated['productname'],
                'brand' => $validated['brand'],
                'color' => $validated['color'],
                'material' => $validated['material'],
                'care_method' => $validated['careMethod'],
                'weight' => $validated['weight'],
                'base_price' => $validated['basePrice'],
                'extensions_price' => $validated['extensionPrice'],
                'deposit' => $validated['deposite'],
                'late_fee' => $validated['lateFee'],
                'replacement_value' => $validated['replacementValue'],
                'keep_to_buy_price' => $validated['keepToBuyPrice'],
                'min_rental_period' => $validated['minRentalPeriod'],
                'max_rental_period' => $validated['maxRentalPeriod'],
                'prep_buffer' => $validated['prepBuffer'],
                'blackout_date' => $validated['date'],
                'location' => $validated['location'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'],
                'fit_category' => $validated['fitType'],
                'chest' => $validated['chest'],
                'length' => $validated['lengthType'],
                'unit' => $validated['unit'],
                'sleeve' => $validated['sleeve'],
                'condition_grade' => $validated['coditionGrade'] ?? null,
                'product_availibity' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);


            if ($request->hasFile('images')) {
                $sortOrder = 0;
                foreach ($request->file('images') as $i => $file) {

                    $path = $file->store('product/images', 'hetzner');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                        'is_primary' => $i === 0 ? 1 : 0,
                    ]);
                }
            }

            if ($request->hasFile('video_file')) {

                $path = $request->file('video_file')->store('product/videos', 'hetzner');

                ProductVideo::create([
                    'product_id' => $product->id,
                    'video_path' => $path,
                    'video_url' => null,
                ]);
            } elseif ($request->filled('video_url')) {

                ProductVideo::create([
                    'product_id' => $product->id,
                    'video_path' => null,
                    'video_url' => $request->video_url,
                ]);
            }


            DB::commit();

            $product->load('images', 'videos');

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product store error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to save product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::with('images', 'videos')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'productname' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'material' => 'required|string|max:255',
            'careMethod' => 'required|string|max:255',
            'weight' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'basePrice' => 'required|numeric',
            'extensionPrice' => 'required|numeric',
            'deposite' => 'required|numeric',
            'lateFee' => 'required|numeric',
            'replacementValue' => 'required|numeric',
            'keepToBuyPrice' => 'required|numeric',
            'minRentalPeriod' => 'required|string|max:100',
            'maxRentalPeriod' => 'required|string|max:100',
            'prepBuffer' => 'required|string|max:100',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'barcode' => 'required|string',
            'fitType' => 'required|string|max:255',
            'chest' => 'required|string|max:100',
            'lengthType' => 'required|string|max:100',
            'sleeve' => 'required|string|max:100',
            'coditionGrade' => 'nullable',
            'status' => 'required|string|max:100',
            'note' => 'nullable|string|max:1000',
            'sizeUnit' => 'required',
            'images.*' => 'nullable|image|max:5120',
            'video_file' => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'video_url' => 'nullable|url|max:1000',
            'keep_images' => 'array', // IDs of images you want to keep
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            // ✅ Update product base info
            $product->update([
                'name' => $validated['productname'],
                'brand' => $validated['brand'],
                'color' => $validated['color'],
                'material' => $validated['material'],
                'care_method' => $validated['careMethod'],
                'weight' => $validated['weight'],
                'base_price' => $validated['basePrice'],
                'extensions_price' => $validated['extensionPrice'],
                'deposit' => $validated['deposite'],
                'late_fee' => $validated['lateFee'],
                'replacement_value' => $validated['replacementValue'],
                'keep_to_buy_price' => $validated['keepToBuyPrice'],
                'min_rental_period' => $validated['minRentalPeriod'],
                'max_rental_period' => $validated['maxRentalPeriod'],
                'prep_buffer' => $validated['prepBuffer'],
                'blackout_date' => $validated['date'],
                'location' => $validated['location'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'],
                'fit_category' => $validated['fitType'],
                'chest' => $validated['chest'],
                'length' => $validated['lengthType'],
                'unit' => $validated['sizeUnit'],
                'sleeve' => $validated['sleeve'],
                'condition_grade' => $validated['coditionGrade'] ?? null,
                'product_availibity' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);


            $keepImages = $validated['keep_images'] ?? [];
            $existingImages = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $keepImages)
                ->get();

            ProductImage::where('product_id', $product->id)
                ->whereNotIn('id', $keepImages)
                ->delete();

            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);

            if ($existingImages->count()) {
                $primaryExists = $existingImages->where('is_primary', true)->first();
                if (!$primaryExists) {
                    $firstImage = $existingImages->first();
                    $firstImage->update(['is_primary' => true]);
                }
            }

            if ($request->hasFile('images')) {

                $primaryExists = ProductImage::where('product_id', $product->id)
                    ->where('is_primary', true)
                    ->exists();

                $sortOrder = ProductImage::where('product_id', $product->id)->max('sort_order') + 1;

                foreach ($request->file('images') as $i => $file) {
                    $path = $file->store('product/images', 'hetzner');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'sort_order' => $sortOrder++,
                        'is_primary' => $primaryExists ? false : ($i === 0),
                    ]);

                    $primaryExists = true;
                }
            }

            if ($request->hasFile('video_file')) {

                ProductVideo::where('product_id', $product->id)->delete();

                $path = $request->file('video_file')->store('product/videos', 'hetzner');
                ProductVideo::create([
                    'product_id' => $product->id,
                    'video_path' => $path,
                    'video_url' => null,
                ]);
            } elseif ($request->filled('video_url')) {
                ProductVideo::updateOrCreate(
                    ['product_id' => $product->id],
                    ['video_path' => null, 'video_url' => $request->video_url]
                );
            }

            DB::commit();

            $product->load('images', 'videos');

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function bulkDelete(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids) || !is_array($ids)) {
        return response()->json([
            'status' => false,
            'message' => 'No valid product IDs provided.',
        ], 400);
    }

    try {
        // 🗑️ Delete multiple products by IDs
        Product::whereIn('id', $ids)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Selected products deleted successfully.',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to delete selected products.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
