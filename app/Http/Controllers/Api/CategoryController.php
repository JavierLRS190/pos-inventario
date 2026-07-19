<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::where('active', true)->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|unique:categories,name',
            'brand' => 'nullable|string|max:100',
        ]);

        $category = Category::create($request->all());
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['active' => false]);
        return response()->json(['message' => 'Categoría desactivada']);
    }
}