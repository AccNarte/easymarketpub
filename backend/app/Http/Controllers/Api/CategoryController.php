<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('listings')->get();

        return response()->json($categories);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('listings'));
    }
}
