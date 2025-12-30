<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseStock;
use App\Models\Warehouse;

class WarehouseController extends Controller
{

     // Return warehouses for a given product_id
public function getWarehouses(Request $request)
{
    $productId = $request->input('product_id');

    $warehouseStocks = WarehouseStock::with('stockWarehouse')
    ->where('product_id', $productId)
    ->get();
// $warehouseStocks = WarehouseStock::where('product_id', $productId)
//     ->get();
 
 $response = $warehouseStocks->map(function ($ws) {
    return [
        'warehouse_id'   => $ws->warehouse_id,
        'warehouse_name' => optional($ws->stockWarehouse)->warehouse_name,
        'stock'          => $ws->quantity,
    ];
});
//    echo "<pre>";
//     print_r($response);
//     echo "</pre>";
// dd();

    return response()->json($response);
}




    // VendorController.php aur WarehouseController.php same hoga
public function index() {
    $warehouses = Warehouse::with('user')->get(); // ya $warehouses = Warehouse::all();
    return view('admin_panel.warehouses.index', compact('warehouses')); // ya warehouses.index
}

public function store(Request $request) {
    if ($request->id) {
        Warehouse::findOrFail($request->id)->update($request->all());
    } else {
        Warehouse::create($request->all());
    }
    return back()->with('success', 'Saved Successfully');
}

public function delete($id) {
    Warehouse::findOrFail($id)->delete();
    return back()->with('success', 'Deleted Successfully');
}

}
