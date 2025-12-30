<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Product;

class StockTransferController extends Controller
{
    public function index() {
        $transfers = StockTransfer::with('fromWarehouse', 'toWarehouse', 'product')->get();
        return view('admin_panel.warehouses.stock_transfers.index', compact('transfers'));
    }

    public function create() {
        $warehouses = Warehouse::all();
        $products = Product::all();
        return view('admin_panel.warehouses.stock_transfers.create', compact('warehouses', 'products'));
    }

      public function store(Request $request)
{
    $request->validate([
        'from_warehouse_id' => 'required|integer',
        'product_id' => 'required|array',
        'product_id.*' => 'required|integer|exists:products,id',
        'quantity' => 'required|array',
        'quantity.*' => 'required|integer|min:1',
    ]);

    $fromWarehouse = $request->from_warehouse_id;
    $toWarehouse = $request->to_warehouse_id;
    $toShop = $request->to_shop ? true : false;
    $remarks = $request->remarks;

    $products = $request->product_id;
    $quantities = $request->quantity;

    foreach ($products as $index => $productId) {
        $qty = $quantities[$index];

        // Check source stock
        $sourceStock = WarehouseStock::where('warehouse_id', $fromWarehouse)
            ->where('product_id', $productId)
            ->first();

        if (!$sourceStock || $sourceStock->quantity < $qty) {
            return back()->with('error', 'Insufficient stock for product ID: ' . $productId);
        }

        // Reduce stock from source
        $sourceStock->quantity -= $qty;
        $sourceStock->save();

        // If not transferring to shop, add to destination warehouse
        if (!$toShop && $toWarehouse) {
            $destStock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $toWarehouse,
                    'product_id' => $productId
                ],
                [
                    'quantity' => 0,
                    'price' => $sourceStock->price
                ]
            );
            $destStock->quantity += $qty;
            $destStock->save();
        }

        // Record each transfer
        StockTransfer::create([
            'from_warehouse_id' => $fromWarehouse,
            'to_warehouse_id' => $toShop ? null : $toWarehouse,
            'to_shop' => $toShop,
            'product_id' => $productId,
            'quantity' => $qty,
            'remarks' => $remarks,
        ]);
    }

    return redirect()->route('stock_transfers.index')->with('success', 'Stock transferred successfully.');
}

    public function destroy(StockTransfer $stockTransfer) {
        // Optional: reverse the transfer if needed
        return back()->with('error', 'Deleting transfers not allowed.');
    }
    public function getStockQuantity(Request $request)
{
    $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
        ->where('product_id', $request->product_id)
        ->first();

    return response()->json([
        'quantity' => $stock ? $stock->quantity : 0
    ]);
}

}



// delvivery challan 
// convet out per  stock ledger maintain