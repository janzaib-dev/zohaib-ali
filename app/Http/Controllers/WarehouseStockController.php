<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;

class WarehouseStockController extends Controller
{
    // /////////
    public function getByWarehouse($warehouseId)
    {
        $products = WarehouseStock::with('product')
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->product->id,
                    'name' => $row->product->item_name,
                    'qty' => $row->quantity,
                ];
            });

        // echo"<pre>";
        // print_r($products);
        // echo"</pre>";
        //         dd();
        return response()->json($products);
    }

    // ////////////////

    public function index()
    {
        $stocks = WarehouseStock::with('warehouse', 'product')->paginate(10);

        return view('admin_panel.warehouses.warehouse_stocks.index', compact('stocks'));
    }

    public function show($id)
    {
        return redirect()->route('warehouse_stocks.index');
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        // Products will be loaded via AJAX Select2
        $products = [];

        return view('admin_panel.warehouses.warehouse_stocks.create', compact('warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $stock = WarehouseStock::create($request->all());

            \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                'product_id' => $stock->product_id,
                'type' => 'adjustment',
                'qty' => $stock->quantity,
                'ref_type' => 'MANUAL_ADJ_INIT',
                'ref_id' => $stock->id,
                'note' => 'Manual Stock Creation',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock added successfully.');
    }

    public function edit(WarehouseStock $warehouseStock)
    {
        $warehouses = Warehouse::all();
        // Only load the current product for the edit form initial state
        $products = Product::where('id', $warehouseStock->product_id)->get();

        return view('admin_panel.warehouses.warehouse_stocks.edit', compact('warehouseStock', 'warehouses', 'products'));
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        $request->validate([
            'warehouse_id' => 'required',
            'product_id' => 'required',
            'quantity' => 'required|integer|min:0',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $warehouseStock) {
            $oldQty = $warehouseStock->quantity;
            $warehouseStock->update($request->all());
            $newQty = $warehouseStock->quantity;
            $delta = $newQty - $oldQty;

            if ($delta != 0) {
                \Illuminate\Support\Facades\DB::table('stock_movements')->insert([
                    'product_id' => $warehouseStock->product_id,
                    'type' => 'adjustment',
                    'qty' => $delta,
                    'ref_type' => 'MANUAL_ADJ_EDIT',
                    'ref_id' => $warehouseStock->id,
                    'note' => 'Manual Stock Adjustment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock updated successfully.');
    }

    public function destroy(WarehouseStock $warehouseStock)
    {
        $warehouseStock->delete();

        return redirect()->route('warehouse_stocks.index')->with('success', 'Stock deleted successfully.');
    }
}
