<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        try {
            $categories = Category::orderBy('name')->get();

            return $this->successResponse([
                'categories' => $categories,
            ], 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve categories', $e->getMessage(), 500);
        }
    }
}
