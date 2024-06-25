<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductUserRating;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = Product::all();
            return response()->json(['status' => true, 'data' => ProductResource::collection($products), 'message' => 'Product list retrieved successfully'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'message' => 'Failed to retrieve product list', 'error' => $error->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
        ], [
            'name.required' => 'Product name is required.',
            'name.unique' => 'Product name already exists.',
            'image.mimes' => 'Invalid file format. Supported formats: jpeg, png, jpg, gif.',
            'image.max' => 'File size cannot exceed 10MB.'
        ]);

        try {
            // Handle file upload
            $filePath = $this->handleImageUpload($request);

            // Create new product
            $product = new Product([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'image' => $filePath,
                'category_id' => $request->category_id,
            ]);

            $product->save();


            // Prepare the response with correct image URL
            if ($filePath) {
                $product->image = Storage::url($filePath);
            }

            return response()->json([
                'status' => true,
                'data' => new ProductResource($product),
                'message' => 'Product created successfully'
            ], 201);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Product creation failed',
                'error' => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
        ], [
            'name.required' => 'Product name is required.',
            'name.unique' => 'Product name already exists.',
            'image.mimes' => 'Invalid file format. Supported formats: jpeg, png, jpg, gif.',
            'image.max' => 'File size cannot exceed 10MB.'
        ]);

        try {
            $product = Product::findOrFail($id);

            // Handle image update
            $filePath = $this->handleImageUpload($request, $product);

            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category_id = $request->category_id;

            $product->save();

            // Prepare the response with correct image URL
            if ($filePath) {
                $product->image = Storage::url($filePath);
            }

            return response()->json([
                'status' => true,
                'data' => new ProductResource($product),
                'message' => 'Product updated successfully'
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Product update failed',
                'error' => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete associated image file if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json(['status' => true, 'message' => 'Product deleted successfully'], 200);
        } catch (Exception $error) {
            return response()->json(['status' => false, 'message' => 'Product deletion failed', 'error' => $error->getMessage()], 400);
        }
    }

    /**
     * Handle image upload and return file path.
     */
    private function handleImageUpload(Request $request, $product = null)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Use the original file name if possible
            $fileName = $file->getClientOriginalName();

            // Generate a unique name only if there's a conflict
            if (Storage::disk('public')->exists('uploads/products/' . $fileName)) {
                $fileName = $this->getUniqueFileName($file);
            }

            // Store the file
            $filePath = $file->storeAs('uploads/products', $fileName, 'public');

            // Delete old image if it exists and is different from the new one
            if ($product && $product->image && $product->image !== $fileName && Storage::disk('public')->exists('uploads/products/' . $product->image)) {
                Storage::disk('public')->delete('uploads/products/' . $product->image);
            }

            return $filePath;
        }

        // No new image uploaded
        return $product ? $product->image : null;
    }

    /**
     * Generate a unique file name to prevent overwriting existing files.
     */
    private function getUniqueFileName($file)
    {
        $fileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Append a timestamp to the file name to ensure uniqueness
        $timestamp = round(microtime(true) * 1000); // Current timestamp in milliseconds
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;

        // Ensure the file name is unique in the storage path
        while (Storage::disk('public')->exists('uploads/products/' . $fileName)) {
            $timestamp = round(microtime(true) * 1000); // Generate new timestamp
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
        }

        return $fileName;
    }


    /**
     * Retrieve ratings for a specific product.
     */



    public function getProductRatings($productId)
    {
        try {
            // Find the product
            $product = Product::findOrFail($productId);

            // Check if the authenticated user (seller) owns this product
            if ($product->user_id !== auth()->id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Fetch ratings associated with this product
            $ratings = ProductUserRating::where('product_id', $productId)
                ->with('user')
                ->get();

            // Format response data
            $formattedRatings = $ratings->map(function ($rating) use ($product) {
                $role = ($rating->user_id === $product->user_id) ? 'owner' : 'customer';

                return [
                    'rating_id' => $rating->id,
                    'user_id' => $rating->user->id,
                    'user_name' => $rating->user->name,
                    'user_email' => $rating->user->email,
                    'rating' => $rating->rating,
                    'role' => $role,
                    'created_at' => $rating->created_at,
                ];
            });


            return response()->json([
                'status' => true,
                'message' => 'Product ratings retrieved successfully',
                'data' => [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'ratings' => $formattedRatings,
                ],
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve product ratings',
                'error' => $error->getMessage(),
            ], 500);
        }
    }
}
