<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        return response()->json(Category::all());
    }
    public function professions(Category $category)
    {
        $professions = $category->professions()
            ->withCount('workers')
            ->get();

        return response()->json($professions);
    }
}
