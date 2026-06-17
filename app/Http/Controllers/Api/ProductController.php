<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'sku'         => 'required|unique:products,sku',
            'price'       => 'required|numeric|min:0',
            'cost'        => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'stock_min'   => 'required|integer|min:0',
            'unit'        => 'required|string',
        ]);

        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'exists:categories,id',
            'name'        => 'string|max:255',
            'sku'         => 'unique:products,sku,' . $id,
            'price'       => 'numeric|min:0',
            'cost'        => 'numeric|min:0',
            'stock'       => 'integer|min:0',
            'stock_min'   => 'integer|min:0',
        ]);

        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['active' => false]);
        return response()->json(['message' => 'Producto desactivado']);
    }

    public function lowStock()
    {
        $products = Product::with('category')
            ->whereColumn('stock', '<=', 'stock_min')
            ->where('active', true)
            ->get();

        return response()->json($products);
    }
}