<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    // Return warehouses for a given product_id
    public function getWarehouses(Request $request)
    {
        $productId = $request->input('product_id');

        // Get all warehouses first
        $allWarehouses = Warehouse::all();
        
        // Get stock entries for this product
        $warehouseStocks = WarehouseStock::with(['stockWarehouse', 'product'])
            ->where('product_id', $productId)
            ->get()
            ->keyBy('warehouse_id');

        $response = $allWarehouses->map(function ($warehouse) use ($warehouseStocks, $productId) {
            $ws = $warehouseStocks->get($warehouse->id);
            $stockVal = 0;
            
            if ($ws) {
                // Self-healing for display: if pieces missing but boxes exist, calculate
                $pieces = $ws->total_pieces;
                $ppb = ($ws->product && $ws->product->pieces_per_box > 0) ? $ws->product->pieces_per_box : 1;

                if ($pieces <= 0 && $ws->quantity > 0) {
                    $pieces = $ws->quantity * $ppb;
                }
                
                $stockVal = $pieces;

                // We return TOTAL PIECES always. 
                // The frontend is responsible for converting to "Boxes.Loose" format 
                // based on the product's size_mode and pieces_per_box available in the DOM.
                // if ($ws->product && ($ws->product->size_mode === 'by_cartons' || $ws->product->size_mode === 'by_size')) {
                //      $stockVal = round($pieces / $ppb, 2);
                // }
            }

            return [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->warehouse_name,
                'stock' => $stockVal,
                'ppb' => $ws && $ws->product ? $ws->product->pieces_per_box : 1,
                'size_mode' => $ws && $ws->product ? $ws->product->size_mode : 'std',
            ];
        });

        return response()->json($response);
    }

    // VendorController.php aur WarehouseController.php same hoga
    public function index()
    {
        if (! auth()->user()->can('warehouse.view')) {
            abort(403, 'Unauthorized action.');
        }
        $warehouses = Warehouse::with('user')->get(); // ya $warehouses = Warehouse::all();

        return view('admin_panel.warehouses.index', compact('warehouses')); // ya warehouses.index
    }

    public function store(Request $request)
    {
        if ($request->id) {
            if (! auth()->user()->can('warehouse.edit')) {
                return back()->with('error', 'Unauthorized action.');
            }
            Warehouse::findOrFail($request->id)->update($request->all());

            return back()->with('success', 'Warehouse Updated Successfully');
        } else {
            if (! auth()->user()->can('warehouse.create')) {
                return back()->with('error', 'Unauthorized action.');
            }
            Warehouse::create($request->all());

            return back()->with('success', 'Warehouse Created Successfully');
        }
    }

    public function delete($id)
    {
        if (! auth()->user()->can('warehouse.delete')) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        Warehouse::findOrFail($id)->delete();

        return response()->json([
            'success' => 'Warehouse Deleted Successfully',
            'reload' => true,
        ]);
    }
}
