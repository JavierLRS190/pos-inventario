<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['product', 'warehouse', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($movements);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type'         => 'required|in:entrada,salida,ajuste',
            'quantity'     => 'required|integer|min:1',
            'reason'       => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'salida' && $product->stock < $request->quantity) {
            return response()->json([
                'message' => "Stock insuficiente. Disponible: {$product->stock}"
            ], 422);
        }

        DB::beginTransaction();
        try {
            $stockBefore = $product->stock;

            if ($request->type === 'entrada') {
                $product->increment('stock', $request->quantity);
            } elseif ($request->type === 'salida') {
                $product->decrement('stock', $request->quantity);
            } else {
                // ajuste directo
                $product->update(['stock' => $request->quantity]);
            }

            $movement = StockMovement::create([
                'product_id'   => $product->id,
                'warehouse_id' => $request->warehouse_id,
                'user_id'      => $request->user()->id,
                'type'         => $request->type,
                'quantity'     => $request->quantity,
                'stock_before' => $stockBefore,
                'stock_after'  => $product->fresh()->stock,
                'reason'       => $request->reason,
            ]);

            DB::commit();
            return response()->json($movement->load(['product', 'warehouse', 'user']), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al registrar movimiento'], 500);
        }
    }
}