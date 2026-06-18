<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Ventas por rango de fechas
    public function salesByRange(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $sales = Sale::with(['user', 'items.product'])
            ->where('status', 'completada')
            ->whereDate('created_at', '>=', $request->from)
            ->whereDate('created_at', '<=', $request->to)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'from'           => $request->from,
            'to'             => $request->to,
            'total_ventas'   => $sales->count(),
            'total_ingresos' => $sales->sum('total'),
            'efectivo'       => $sales->where('payment_method', 'efectivo')->sum('total'),
            'tarjeta'        => $sales->where('payment_method', 'tarjeta')->sum('total'),
            'transferencia'  => $sales->where('payment_method', 'transferencia')->sum('total'),
            'ventas'         => $sales,
        ]);
    }

    // Productos más vendidos
    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);

        $products = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_vendido'),
                DB::raw('SUM(subtotal) as total_ingresos')
            )
            ->with('product.category')
            ->whereHas('sale', fn($q) => $q->where('status', 'completada'))
            ->groupBy('product_id')
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($products);
    }

    // Corte de caja
    public function cashCut(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $sales = Sale::with('items')
            ->whereDate('created_at', $date)
            ->get();

        $completadas = $sales->where('status', 'completada');
        $canceladas  = $sales->where('status', 'cancelada');

        return response()->json([
            'fecha'              => $date,
            'ventas_completadas' => $completadas->count(),
            'ventas_canceladas'  => $canceladas->count(),
            'total_ingresos'     => $completadas->sum('total'),
            'total_efectivo'     => $completadas->where('payment_method', 'efectivo')->sum('total'),
            'total_tarjeta'      => $completadas->where('payment_method', 'tarjeta')->sum('total'),
            'total_transferencia'=> $completadas->where('payment_method', 'transferencia')->sum('total'),
            'total_articulos'    => $completadas->sum(fn($s) => $s->items->sum('quantity')),
        ]);
    }

    // Stock actual de todos los productos
    public function stockReport()
    {
        $products = Product::with('category')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'nombre'      => $p->name,
                'sku'         => $p->sku,
                'categoria'   => $p->category->name,
                'stock'       => $p->stock,
                'stock_min'   => $p->stock_min,
                'alerta'      => $p->stock <= $p->stock_min,
                'precio'      => $p->price,
                'valor_total' => $p->stock * $p->cost,
            ]);

        return response()->json([
            'total_productos'    => $products->count(),
            'productos_en_alerta'=> $products->where('alerta', true)->count(),
            'valor_inventario'   => $products->sum('valor_total'),
            'productos'          => $products,
        ]);
    }
}