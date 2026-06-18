<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($sales);
    }

    public function show($id)
    {
        $sale = Sale::with(['user', 'items.product'])->findOrFail($id);
        return response()->json($sale);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
            'paid'           => 'required|numeric|min:0',
        ]);

        // Verificar stock antes de procesar
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'message' => "Stock insuficiente para: {$product->name}. Disponible: {$product->stock}"
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $subtotal += $product->price * $item['quantity'];
            }

            $tax   = 0; // puedes agregar IVA después
            $total = $subtotal + $tax;

            if ($request->paid < $total) {
                return response()->json(['message' => 'El monto pagado es insuficiente'], 422);
            }

            // Generar folio
            $lastSale = Sale::orderBy('id', 'desc')->first();
            $folioNum = $lastSale ? (intval(substr($lastSale->folio, 4)) + 1) : 1;
            $folio    = 'VTA-' . str_pad($folioNum, 4, '0', STR_PAD_LEFT);

            // Crear la venta
            $sale = Sale::create([
                'user_id'        => $request->user()->id,
                'folio'          => $folio,
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'total'          => $total,
                'paid'           => $request->paid,
                'change'         => $request->paid - $total,
                'payment_method' => $request->payment_method,
                'status'         => 'completada',
                'notes'          => $request->notes,
            ]);

            // Crear items y descontar stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal'   => $product->price * $item['quantity'],
                ]);

                // Descontar stock
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json(
                Sale::with(['user', 'items.product'])->find($sale->id),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al procesar la venta', 'error' => $e->getMessage()], 500);
        }
    }

    public function cancel($id)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->status === 'cancelada') {
            return response()->json(['message' => 'La venta ya está cancelada'], 422);
        }

        DB::beginTransaction();
        try {
            // Regresar stock
            foreach ($sale->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelada']);
            DB::commit();

            return response()->json(['message' => 'Venta cancelada y stock restaurado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al cancelar la venta'], 500);
        }
    }

    public function todaySummary()
    {
        $today = today();

        $sales = Sale::where('status', 'completada')
            ->whereDate('created_at', $today)
            ->get();

        return response()->json([
            'fecha'         => $today->format('d/m/Y'),
            'total_ventas'  => $sales->count(),
            'total_ingresos'=> $sales->sum('total'),
            'efectivo'      => $sales->where('payment_method', 'efectivo')->sum('total'),
            'tarjeta'       => $sales->where('payment_method', 'tarjeta')->sum('total'),
            'transferencia' => $sales->where('payment_method', 'transferencia')->sum('total'),
        ]);
    }
}