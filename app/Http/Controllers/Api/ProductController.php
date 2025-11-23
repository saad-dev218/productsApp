<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    use ApiResponseTrait;

    private function processImages($images, $product, $replace = false)
    {
        if ($replace) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        $uploadedImages = [];
        $maxSortOrder = $product->images()->max('sort_order') ?? 0;

        foreach ($images as $index => $image) {
            $imageName = (int)(microtime(true) * 10000) . '_' . uniqid() . '_' . $index . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $imageName, 'public');

            $fullImagePath = storage_path('app/public/' . $imagePath);
            $manager = new ImageManager(new Driver());
            $resizedImage = $manager->read($fullImagePath);
            $resizedImage->scale(width: 800, height: 800);
            $resizedImage->save($fullImagePath);

            $maxSortOrder++;

            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imagePath,
                'sort_order' => $maxSortOrder,
            ]);

            $uploadedImages[] = $productImage;
        }

        return $uploadedImages;
    }

    public function index(Request $request)
    {
        try {
            $query = Product::with(['images', 'category']);

            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->has('category')) {
                $query->where('category_id', $request->category);
            }

            if ($request->has('availability')) {
                if ($request->availability === 'in_stock') {
                    $query->inStock();
                } elseif ($request->availability === 'out_of_stock') {
                    $query->outOfStock();
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $allowedSortFields = ['name', 'price', 'created_at', 'stock'];
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->get('limit', 15);
            $perPage = min(max(1, (int)$perPage), 100);

            $products = $query->paginate($perPage);

            return $this->successResponse([
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
            ], 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products', $e->getMessage(), 500);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'user_id' => auth()->id(),
            ]);

            if ($request->hasFile('images')) {
                $this->processImages($request->file('images'), $product);
            }

            return $this->successResponse([
                'product' => $product->fresh()->load(['images', 'category', 'user']),
            ], 'Product created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create product', $e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        try {
            $product = Product::with(['images', 'category'])->findOrFail($id);

            return $this->successResponse([
                'product' => $product,
            ], 'Product retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product', $e->getMessage(), 500);
        }
    }

    public function update(ProductRequest $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->user_id !== auth()->id()) {
                return $this->errorResponse('You can only update your own products', null, 403);
            }

            $updateData = [];
            
            if ($request->has('name') && $request->filled('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }
            if ($request->has('price') && $request->filled('price')) {
                $updateData['price'] = $request->price;
            }
            if ($request->has('stock') && $request->filled('stock')) {
                $updateData['stock'] = $request->stock;
            }
            if ($request->has('category_id')) {
                $categoryId = $request->category_id;
                $updateData['category_id'] = ($categoryId === '' || $categoryId === null) ? null : (int)$categoryId;
            }

            if (!empty($updateData)) {
                $product->update($updateData);
            }

            if ($request->hasFile('images')) {
                $this->processImages($request->file('images'), $product, true);
            }

            return $this->successResponse([
                'product' => $product->fresh()->load(['images', 'category', 'user']),
            ], 'Product updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update product', $e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $product = Product::with('images')->findOrFail($id);

            if ($product->user_id !== auth()->id()) {
                return $this->errorResponse('You can only delete your own products', null, 403);
            }

            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $product->delete();

            return $this->successResponse(null, 'Product deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete product', $e->getMessage(), 500);
        }
    }

    public function uploadImage(ImageUploadRequest $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);

            if ($product->user_id !== auth()->id()) {
                return $this->errorResponse('You can only upload images to your own products', null, 403);
            }

            $uploadedImages = $this->processImages($request->file('images'), $product);

            return $this->successResponse([
                'product' => $product->fresh()->load('images'),
                'images' => $uploadedImages,
            ], 'Images uploaded successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found', null, 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload images', $e->getMessage(), 500);
        }
    }
}
