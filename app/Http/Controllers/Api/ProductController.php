<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Trait\ApiResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Fetch all Products.
     */
    public function index()
    {
        try {
            $products = Product::with('images', 'videos', 'partner')->get();
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
}
