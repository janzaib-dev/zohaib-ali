<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\PurchaseItem;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use App\Models\VendorLedger;
use App\Models\Inwardgatepass;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /** Keep stocks table in sync for a (branch,warehouse,product) */
private function upsertStocks(int $productId, float $qtyDelta, int $branchId, int $warehouseId): void
{
    $affected = DB::table('stocks')
        ->where('product_id', $productId)
        ->where('branch_id', $branchId)
        ->where('warehouse_id', $warehouseId)
        ->update([
            'qty'        => DB::raw('qty + ' . ($qtyDelta + 0)),
            'updated_at' => now(),
        ]);

    if ($affected === 0) {
        DB::table('stocks')->insert([
            'product_id'   => $productId,
            'branch_id'    => $branchId,
            'warehouse_id' => $warehouseId,
            'qty'          => $qtyDelta,
            'reserved_qty' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}

    public function index()
    {
        $Purchase = Purchase::with(['branch', 'warehouse', 'vendor', 'items'])->get();
        return view("admin_panel.purchase.index", compact('Purchase'));
    }
    public function addBill($gatepassId)
    {
        // Fetch the gatepass along with its related items and products
        $gatepass = InwardGatepass::with('items.product')->findOrFail($gatepassId);

        // Pass the gatepass data to the view
        return view('admin_panel.inward.add_bill', compact('gatepass'));
    }

    public function add_purchase()
    {
        // $userId = Auth::id();
        $Purchase = Purchase::get();
        $Vendor = Vendor::get();
        $Warehouse = Warehouse::get();
        return view('admin_panel.purchase.add_purchase', compact('Vendor', "Warehouse", 'Purchase'));
    }
   public function store(Request $request, $gatepassId = null)
{
    // (A) Gatepass fetch if provided
    $gatepass = null;
    if ($gatepassId) {
        $gatepass = \App\Models\InwardGatepass::with('purchase')->findOrFail($gatepassId);
        if ($gatepass->purchase) {
            return back()->with('error', 'This gatepass already has an associated bill.');
        }
    }

    // (B) Validation (branch_id bhi lein, warehouse_id to already hai)
    $validated = $request->validate([
        'invoice_no'      => 'nullable|string',
        'vendor_id'       => 'nullable|exists:vendors,id',
        'purchase_date'   => 'nullable|date',
        'branch_id'       => 'nullable|exists:branches,id',
        'warehouse_id'    => 'required|exists:warehouses,id',
        'note'            => 'nullable|string',
        'discount'        => 'nullable|numeric|min:0',
        'extra_cost'      => 'nullable|numeric|min:0',

        'product_id'      => 'array',
        'product_id.*'    => 'nullable|exists:products,id',
        'qty'             => 'array',
        'qty.*'           => 'nullable|required_with:product_id.*|numeric|min:1',
        'price'           => 'array',
        'price.*'         => 'nullable|required_with:product_id.*|numeric|min:0',
        'unit'            => 'array',
        'unit.*'          => 'nullable|required_with:product_id.*|string',
        'item_discount'   => 'nullable|array',
        'item_discount.*' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($validated, $request, $gatepass) {
        // invoice number
        $lastInvoice = Purchase::latest()->value('invoice_no');
        $nextInvoice = $lastInvoice
            ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
            : 'INV-00001';

        $branchId    = (int)($validated['branch_id'] ?? 1);                 // ✅ use real branch
        $warehouseId = (int)$validated['warehouse_id'];

        // create header
        $purchase = Purchase::create([
            'branch_id'     => $branchId,
            'warehouse_id'  => $warehouseId,
            'vendor_id'     => $validated['vendor_id'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? now(),
            'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
            'note'          => $validated['note'] ?? null,
            'subtotal'      => 0,
            'discount'      => 0,
            'extra_cost'    => 0,
            'net_amount'    => 0,
            'paid_amount'   => 0,
            'due_amount'    => 0,
        ]);

        $subtotal = 0;
        $pids = $validated['product_id'] ?? [];
        $qtys = $validated['qty'] ?? [];
        $prices = $validated['price'] ?? [];
        $units = $validated['unit'] ?? [];
        $itemDiscs = $validated['item_discount'] ?? [];

        $movRows = [];  // only for direct purchase

        foreach ($pids as $i => $pid) {
            $pid = (int)($pid ?? 0);
            $qty = (float)($qtys[$i] ?? 0);
            $price = (float)($prices[$i] ?? 0);
            if (!$pid || $qty <= 0 || $price < 0) continue;

            $disc = (float)($itemDiscs[$i] ?? 0);
            $unit = $units[$i] ?? null;
            $lineTotal = ($price * $qty) - $disc;

            PurchaseItem::create([
                'purchase_id'   => $purchase->id,
                'product_id'    => $pid,
                'unit'          => $unit,
                'price'         => $price,
                'item_discount' => $disc,
                'qty'           => $qty,
                'line_total'    => $lineTotal,
            ]);

            $subtotal += $lineTotal;

            // ✅ STOCK: if gatepass linked, DO NOTHING (already inward added it)
            if (!$gatepass) {
                // movement (+)
                $movRows[] = [
                    'product_id' => $pid,
                    'type'       => 'in',
                    'qty'        => $qty,
                    'ref_type'   => 'PURCHASE',
                    'ref_id'     => $purchase->id,
                    'note'       => 'Direct purchase',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                // stocks
                $this->upsertStocks($pid, +$qty, $branchId, $warehouseId);
            }
        }

        // insert movements (direct purchase only)
        if (!$gatepass && !empty($movRows)) {
            DB::table('stock_movements')->insert($movRows);
        }

        // totals
        $discount  = (float)($request->discount ?? 0);
        $extraCost = (float)($request->extra_cost ?? 0);
        $netAmount = ($subtotal - $discount) + $extraCost;

        $purchase->update([
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'extra_cost'  => $extraCost,
            'net_amount'  => $netAmount,
            'due_amount'  => $netAmount,
        ]);

        // Vendor ledger
        $prevClosing = \App\Models\VendorLedger::where('vendor_id', $validated['vendor_id'] ?? null)
            ->value('closing_balance') ?? 0;
        \App\Models\VendorLedger::updateOrCreate(
            ['vendor_id' => $validated['vendor_id'] ?? null],
            [
                'vendor_id'         => $validated['vendor_id'] ?? null,
                'admin_or_user_id'  => auth()->id(),
                'previous_balance'  => $prevClosing,
                'opening_balance'   => $prevClosing,
                'closing_balance'   => $prevClosing + $netAmount,
            ]
        );

        // link gatepass -> purchase (and keep status)
        if ($gatepass) {
            $gatepass->purchase_id = $purchase->id;
            $gatepass->status = 'linked';
            $gatepass->save();
        }
    });

    return redirect()->route('Purchase.home')->with('success', 'Purchase saved successfully.');
}







    // public function store(Request $request)
    // {
    //     // ✅ Validation
    //     $validated = $request->validate([
    //         'invoice_no'     => 'nullable|string',
    //         'vendor_id'      => 'nullable|exists:vendors,id',
    //         'purchase_date'  => 'nullable|date',
    //         'warehouse_id'   => 'nullable|exists:warehouses,id',
    //         'note'           => 'nullable|string',
    //         'discount'       => 'nullable|numeric|min:0',
    //         'extra_cost'     => 'nullable|numeric|min:0',

    //         // Purchase Items
    //         'product_id'       => 'nullable|array',
    //         'product_id.*'     => 'nullable|exists:products,id',
    //         'qty'              => 'nullable|array',
    //         'qty.*'            => 'nullable|numeric|min:1',
    //         'price'            => 'nullable|array',
    //         'price.*'          => 'nullable|numeric|min:0',
    //         'unit'             => 'nullable|array',
    //         'unit.*'           => 'nullable|string',
    //         'item_discount'    => 'nullable|array',
    //         'item_discount.*'  => 'nullable|numeric|min:0',
    //     ]);

    //     DB::transaction(function () use ($validated, $request) {

    //         // 🧾 Generate Next Invoice No
    //         $lastInvoice = Purchase::latest()->value('invoice_no');
    //         $nextInvoice = $lastInvoice
    //             ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    //             : 'INV-00001';

    //         // ✍️ Create Purchase with temporary values
    //         $purchase = Purchase::create([
    //             'branch_id'     => auth()->user()->id,
    //             'warehouse_id'  => $validated['warehouse_id'],
    //             'vendor_id'     => $validated['vendor_id'] ?? null,
    //             'purchase_date' => $validated['purchase_date'] ?? now(),
    //             'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    //             'note'          => $validated['note'] ?? null,
    //             'subtotal'      => 0,
    //             'discount'      => 0,
    //             'extra_cost'    => 0,
    //             'net_amount'    => 0,
    //             'paid_amount'   => 0,
    //             'due_amount'    => 0,
    //         ]);

    //         $subtotal = 0;

    //         // 🧾 Purchase Items
    //         $productIds = $validated['product_id'] ?? [];
    //         foreach ($productIds as $index => $productId) {
    //             $qty   = $validated['qty'][$index] ?? null;
    //             $price = $validated['price'][$index] ?? null;

    //             if (empty($productId) || empty($qty) || empty($price)) {
    //                 continue;
    //             }

    //             $disc = $validated['item_discount'][$index] ?? 0; // ✅ Correct name
    //             $unit = $validated['unit'][$index] ?? null;

    //             $lineTotal = ($price * $qty) - $disc;

    //             PurchaseItem::create([
    //                 'purchase_id'   => $purchase->id,
    //                 'product_id'    => $productId,
    //                 'unit'          => $unit,
    //                 'price'         => $price,
    //                 'item_discount' => $disc,
    //                 'qty'           => $qty,
    //                 'line_total'    => $lineTotal,
    //             ]);

    //             $subtotal += $lineTotal;

    //             // 📦 Update Stock
    //             $stock = Stock::where('branch_id', auth()->user()->id)
    //                 ->where('warehouse_id', $validated['warehouse_id'])
    //                 ->where('product_id', $productId)
    //                 ->first();

    //             if ($stock) {
    //                 $stock->qty += $qty;
    //                 $stock->save();
    //             } else {
    //                 Stock::create([
    //                     'branch_id'     => auth()->user()->id,
    //                     'warehouse_id'  => $validated['warehouse_id'],
    //                     'product_id'    => $productId,
    //                     'qty'           => $qty,
    //                 ]);
    //             }
    //         }

    //         // 💵 Final Calculations (use values from request safely)
    //         $discount   = $request->discount ?? 0;
    //         $extraCost  = $request->extra_cost ?? 0;
    //         $netAmount  = ($subtotal - $discount) + $extraCost;

    //         $purchase->update([
    //             'subtotal'    => $subtotal,
    //             'discount'    => $discount,
    //             'extra_cost'  => $extraCost,
    //             'net_amount'  => $netAmount,
    //             'due_amount'  => $netAmount,
    //         ]);

    //         // 📘 Vendor Ledger Update
    //         $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    //             ->value('closing_balance') ?? 0;

    //         $newClosingBalance = $previousBalance + $netAmount;

    //         VendorLedger::updateOrCreate(
    //             ['vendor_id' => $validated['vendor_id']],
    //             [
    //                 'vendor_id'         => $validated['vendor_id'],
    //                 'admin_or_user_id'  => auth()->id(),
    //                 'previous_balance'  => $subtotal,
    //                 'closing_balance'   => $newClosingBalance,
    //             ]
    //         );
    //     });

    //     return back()->with('success', 'Purchase saved successfully!');
    // }


    // public function store(Request $request)
    // {

    //         $validated = $request->validate([
    //             'invoice_no'     => 'nullable|string',
    //             'vendor_id'      => 'nullable|exists:vendors,id',
    //             // 'branch_id'      => 'required|exists:branches,id',
    //             'purchase_date'  => 'nullable|date',
    //             'warehouse_id'   => 'nullable|exists:warehouses,id',
    //             'note'           => 'nullable|string',
    //     'discount'       => 'nullable|numeric|min:0',
    //     'extra_cost'     => 'nullable|numeric|min:0',

    //             // Purchase Items
    //             'product_id'     => 'nullable|array',
    //             'product_id.*'   => 'nullable|exists:products,id',
    //             'qty'            => 'nullable|array',
    //             'qty.*'          => 'nullable|numeric|min:1',
    //             'price'          => 'nullable|array',
    //             'price.*'        => 'nullable|numeric|min:0',
    //             'unit'           => 'nullable|array',
    //             'unit.*'         => 'nullable|string',
    //             'item_discount'  => 'nullable|array',
    //             'item_discount.*'=> 'nullable|numeric|min:0',
    //         ]);
    // DB::transaction(function () use ($validated) {

    //     $lastInvoice = Purchase::latest()->value('invoice_no');

    //     $nextInvoice = $lastInvoice
    //         ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    //         : 'INV-00001';

    //     // 1️⃣ Create purchase
    //     $purchase = Purchase::create([
    //         'branch_id'     => Auth()->user()->id,
    //         'warehouse_id'  => $validated['warehouse_id'],
    //         'vendor_id'     => $validated['vendor_id'] ?? null,
    //         'purchase_date' => $validated['purchase_date'] ?? now(),
    //         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    //         'note'          => $validated['note'] ?? null,
    //         'subtotal'      => $validated['subtotal'] ?? 0,
    //         'discount'      => $validated['discount'] ?? 0,
    //         'extra_cost'    => $validated['extra_cost'] ?? 0,
    //         'net_amount'    => $validated['net_amount'] ?? 0,
    //         'paid_amount'   => 0,
    //         'due_amount'    => 0,

    //     ]);

    //     $subtotal = 0;

    //     // 2️⃣ Loop & filter rows
    //     $productIds = $validated['product_id'] ?? [];
    //     foreach ($productIds as $index => $productId) {
    //         $qty   = $validated['qty'][$index] ?? null;
    //         $price = $validated['price'][$index] ?? null;

    //         // Skip row if any essential field is empty
    //         if (empty($productId) || empty($qty) || empty($price)) {
    //             continue;
    //         }

    //         $disc = $validated['item_disc'][$index] ?? 0;
    //         $unit = $validated['unit'][$index] ?? null;

    //         $lineTotal = ($price * $qty) - $disc;

    //         // Save item
    //         PurchaseItem::create([
    //             'purchase_id'   => $purchase->id,
    //             'product_id'    => $productId,
    //             'unit'          => $unit,
    //             'price'         => $price,
    //             'item_discount' => $disc,
    //             'qty'           => $qty,
    //             'line_total'    => $lineTotal,
    //         ]);

    //         $subtotal += $lineTotal;

    //         // 3️⃣ Update stock
    //         $stock = Stock::where('branch_id', Auth()->user()->id)
    //             ->where('warehouse_id', $validated['warehouse_id'])
    //             ->where('product_id', $productId)
    //             ->first();

    //         if ($stock) {
    //             $stock->qty += $qty;
    //             $stock->save();
    //         } else {
    //             Stock::create([
    //                 'branch_id'     => Auth()->user()->id,
    //                 'warehouse_id'  => $validated['warehouse_id'],
    //                 'product_id'    => $productId,
    //                 'qty'           => $qty,
    //             ]);
    //         }
    //     }

    //     // 4️⃣ Update totals
    //     $purchase->update([
    //         'subtotal'    => $subtotal,
    //         'net_amount'  => $subtotal,
    //         'due_amount'  => $subtotal,
    //     ]);

    //     // 5️⃣ Vendor ledger
    //     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    //         ->value('closing_balance') ?? 0;

    //     $newClosingBalance = $previousBalance + $subtotal;

    //     VendorLedger::updateOrCreate(
    //         ['vendor_id' => $validated['vendor_id']],
    //         [
    //             'vendor_id' => $validated['vendor_id'],
    //             'admin_or_user_id' => Auth::id(),
    //             'previous_balance' => $subtotal,
    //             'closing_balance' => $newClosingBalance,
    //         ]
    //     );

    // });

    // // DB::transaction(function () use ($validated) {

    // // $lastInvoice = Purchase::latest()->value('invoice_no');

    // // // Agar last invoice mila to +1 karo, warna start karo INV-00001
    // // $nextInvoice = $lastInvoice
    // //     ? 'INV-' . str_pad(((int) filter_var($lastInvoice, FILTER_SANITIZE_NUMBER_INT)) + 1, 5, '0', STR_PAD_LEFT)
    // //     : 'INV-00001';

    // //     // 1️⃣ Save main Purchase
    // //     $purchase = Purchase::create([

    // //         'branch_id'     => Auth()->user()->id,
    // //         'warehouse_id'  => $validated['warehouse_id'],
    // //         'vendor_id'     => $validated['vendor_id'] ?? null,
    // //         'purchase_date' => $validated['purchase_date'] ?? now(),
    // //         'invoice_no'    => $validated['invoice_no'] ?? $nextInvoice,
    // //         'note'          => $validated['note'] ?? null,
    // //         'subtotal'      => 0,
    // //         'discount'      => 0,
    // //         'extra_cost'    => 0,
    // //         'net_amount'    => 0,
    // //         'paid_amount'   => 0,
    // //         'due_amount'    => 0,
    // //     ]);

    // //     $subtotal = 0;

    // //     // 2️⃣ Loop purchase items
    // //     foreach ($validated['product_id'] as $index => $productId) {
    // //         $qty     = $validated['qty'][$index];
    // //         $price   = $validated['price'][$index];
    // //         $disc    = $validated['item_discount'][$index] ?? 0;
    // //         $lineTotal = ($price * $qty) - $disc;

    // //         // Save purchase item
    // //         PurchaseItem::create([
    // //             'purchase_id'   => $purchase->id,
    // //             'product_id'    => $productId,
    // //             'unit'          => $validated['unit'][$index] ?? null,
    // //             'price'         => $price,
    // //             'item_discount' => $disc,
    // //             'qty'           => $qty,
    // //             'line_total'    => $lineTotal,
    // //         ]);

    // //         $subtotal += $lineTotal;

    // //         // 3️⃣ Update stock
    // //         $stock = Stock::where('branch_id',  Auth()->user()->id,)
    // //             ->where('warehouse_id', $validated['warehouse_id'])
    // //             ->where('product_id', $productId)
    // //             ->first();

    // //         if ($stock) {
    // //             $stock->qty += $qty;
    // //             $stock->save();
    // //         } else {
    // //             Stock::create([
    // //                 'branch_id'     => Auth()->user()->id,
    // //                 'warehouse_id'  => $validated['warehouse_id'],
    // //                 'product_id'    => $productId,
    // //                 'qty'           => $qty,
    // //             ]);
    // //         }
    // //     }

    // //     // 4️⃣ Update totals in purchase
    // //     $purchase->update([
    // //         'subtotal'    => $subtotal,
    // //         'net_amount'  => $subtotal,
    // //         'due_amount'  => $subtotal,
    // //     ]);

    // //     $previousBalance = VendorLedger::where('vendor_id', $validated['vendor_id'])
    // //         ->value('closing_balance') ?? 0; // If no previous balance, start from 0
    // //     // Calculate new balances

    // //     $newPreviousBalance = $subtotal;

    // //     $newClosingBalance = $previousBalance + $subtotal;
    // //     $userId = Auth::id();

    // //     // Update or create distributor ledger
    // //     VendorLedger::updateOrCreate(
    // //         ['vendor_id' => $validated['vendor_id']],
    // //         [
    // //             'vendor_id' => $validated['vendor_id'],
    // //             'admin_or_user_id' => $userId,
    // //             'previous_balance' => $newPreviousBalance,
    // //             'closing_balance' => $newClosingBalance,
    // //         ]
    // //     );

    // });

    //     return redirect()->back()->with('success', 'Purchase saved successfully!');
    // }


    public function edit($id)
    {
        $purchase   = Purchase::with('items.product')->findOrFail($id);
        $Vendor     = Vendor::all();
        $Warehouse  = Warehouse::all();

        return view('admin_panel.purchase.edit', compact('purchase', 'Vendor', 'Warehouse'));
    }



   public function update(Request $request, $id)
{
    $validated = $request->validate([
        'invoice_no'      => 'nullable|string',
        'vendor_id'       => 'nullable|exists:vendors,id',
        'purchase_date'   => 'nullable|date',
        'branch_id'       => 'nullable|exists:branches,id',
        'warehouse_id'    => 'required|exists:warehouses,id',
        'note'            => 'nullable|string',
        'discount'        => 'nullable|numeric|min:0',
        'extra_cost'      => 'nullable|numeric|min:0',

        'product_id'      => 'array',
        'product_id.*'    => 'nullable|exists:products,id',
        'qty'             => 'array',
        'qty.*'           => 'nullable|required_with:product_id.*|numeric|min:1',
        'price'           => 'array',
        'price.*'         => 'nullable|required_with:product_id.*|numeric|min:0',
        'unit'            => 'array',
        'unit.*'          => 'nullable|required_with:product_id.*|string',
        'item_discount'   => 'nullable|array',
        'item_discount.*' => 'nullable|numeric|min:0',
    ]);

    DB::transaction(function () use ($validated, $request, $id) {
        $purchase = Purchase::with('items')->findOrFail($id);

        $branchId    = (int)($validated['branch_id'] ?? $purchase->branch_id ?? 1);
        $warehouseId = (int)($validated['warehouse_id'] ?? $purchase->warehouse_id);

        // Map old totals per product
        $oldMap = $purchase->items->groupBy('product_id')->map(fn($g)=> (float)$g->sum('qty'));

        // Rebuild items
        $purchase->items()->delete();

        $subtotal = 0;
        $newMap = collect();

        $pids = $validated['product_id'] ?? [];
        $qtys = $validated['qty'] ?? [];
        $prices = $validated['price'] ?? [];
        $units = $validated['unit'] ?? [];
        $itemDiscs = $validated['item_discount'] ?? [];

        foreach ($pids as $i => $pid) {
            $pid = (int)($pid ?? 0);
            $qty = (float)($qtys[$i] ?? 0);
            $price = (float)($prices[$i] ?? 0);
            if (!$pid || $qty <= 0 || $price < 0) continue;

            $disc = (float)($itemDiscs[$i] ?? 0);
            $unit = $units[$i] ?? null;
            $lineTotal = ($price * $qty) - $disc;

            PurchaseItem::create([
                'purchase_id'   => $purchase->id,
                'product_id'    => $pid,
                'unit'          => $unit,
                'price'         => $price,
                'item_discount' => $disc,
                'qty'           => $qty,
                'line_total'    => $lineTotal,
            ]);

            $subtotal += $lineTotal;
            $newMap[$pid] = ($newMap[$pid] ?? 0) + $qty;
        }

        // header update
        $purchase->update([
            'vendor_id'     => $validated['vendor_id'] ?? $purchase->vendor_id,
            'branch_id'     => $branchId,
            'warehouse_id'  => $warehouseId,
            'purchase_date' => $validated['purchase_date'] ?? $purchase->purchase_date,
            'invoice_no'    => $validated['invoice_no'] ?? $purchase->invoice_no,
            'note'          => $validated['note'] ?? $purchase->note,
        ]);

        // totals
        $discount  = (float)($request->discount ?? 0);
        $extraCost = (float)($request->extra_cost ?? 0);
        $netAmount = ($subtotal - $discount) + $extraCost;

        $purchase->update([
            'subtotal'    => $subtotal,
            'discount'    => $discount,
            'extra_cost'  => $extraCost,
            'net_amount'  => $netAmount,
            'due_amount'  => $netAmount,
        ]);

        // If this purchase is linked to a gatepass => NO stock changes here
        $isLinkedToGatepass = \App\Models\InwardGatepass::where('purchase_id', $purchase->id)->exists();

        if (!$isLinkedToGatepass) {
            // deltas for movements + stocks
            $movs = [];
            $now = now();
            $all = $oldMap->keys()->merge($newMap->keys())->unique();
            foreach ($all as $pid) {
                $oldQ = (float)($oldMap[$pid] ?? 0);
                $newQ = (float)($newMap[$pid] ?? 0);
                $delta = $newQ - $oldQ;
                if ($delta == 0) continue;

                $type = $delta > 0 ? 'in' : 'out';
                $qty  = abs($delta);

                $movs[] = [
                    'product_id' => (int)$pid,
                    'type'       => $type,
                    'qty'        => $qty,
                    'ref_type'   => 'PURCHASE_EDIT',
                    'ref_id'     => $purchase->id,
                    'note'       => 'Purchase edit delta',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $this->upsertStocks((int)$pid, ($type==='in' ? +$qty : -$qty), $branchId, $warehouseId);
            }
            if (!empty($movs)) {
                DB::table('stock_movements')->insert($movs);
            }
        }

        // Vendor ledger (simple overwrite pattern)
        $prevClosing = \App\Models\VendorLedger::where('vendor_id', $purchase->vendor_id)
            ->value('closing_balance') ?? 0;
        \App\Models\VendorLedger::updateOrCreate(
            ['vendor_id' => $purchase->vendor_id],
            [
                'vendor_id'         => $purchase->vendor_id,
                'admin_or_user_id'  => auth()->id(),
                'previous_balance'  => $prevClosing,
                'opening_balance'   => $prevClosing,
                'closing_balance'   => $prevClosing + $netAmount,
            ]
        );
    });

    return redirect()->route('Purchase.home')->with('success', 'Purchase updated successfully!');
}



public function destroy($id)
{
    DB::transaction(function () use ($id) {
        $purchase = Purchase::with('items')->findOrFail($id);

        $branchId    = (int)($purchase->branch_id ?? 1);
        $warehouseId = (int)($purchase->warehouse_id);

        // linked to gatepass? then NO stock changes
        $isLinkedToGatepass = \App\Models\InwardGatepass::where('purchase_id', $purchase->id)->exists();

        if (!$isLinkedToGatepass) {
            $movs = [];
            $now = now();

            foreach ($purchase->items as $it) {
                $pid = (int)$it->product_id;
                $qty = (float)$it->qty;

                $movs[] = [
                    'product_id' => $pid,
                    'type'       => 'out',
                    'qty'        => $qty,
                    'ref_type'   => 'PURCHASE_DELETE',
                    'ref_id'     => $purchase->id,
                    'note'       => 'Delete purchase (reverse)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // stocks rollback
                $this->upsertStocks($pid, -$qty, $branchId, $warehouseId);
            }

            if (!empty($movs)) {
                DB::table('stock_movements')->insert($movs);
            }
        }

        $purchase->items()->delete();
        $purchase->delete();
    });

    return redirect()->back()->with('success', 'Purchase deleted successfully.');
}



    public function Invoice($id)
    {
        $purchase   = Purchase::with('items.product')->findOrFail($id);
        $Vendor     = Vendor::all();
        $Warehouse  = Warehouse::all();

        return view('admin_panel.purchase.Invoice', compact('purchase', 'Vendor', 'Warehouse'));
    }





    // purchase_reutun



    public function showReturnForm($id)
    {
        $purchase = Purchase::with(['vendor', 'warehouse', 'items.product'])->findOrFail($id);
        $Vendor = \App\Models\Vendor::all();
        $Warehouse = \App\Models\Warehouse::all();

        return view('admin_panel.purchase.purchase_return.create', compact('purchase', 'Vendor', 'Warehouse'));
    }

    // store return
    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'vendor_id'        => 'required|exists:vendors,id',
            'warehouse_id'     => 'required|exists:warehouses,id',
            'return_date'      => 'required|date',
            'return_reason'    => 'nullable|string|max:255',
            'remarks'          => 'nullable|string',
            'product_id'       => 'required|array',
            'product_id.*'     => 'required|exists:products,id',
            'qty'              => 'required|array',
            'qty.*'            => 'required|numeric|min:1',
            'price'            => 'required|array',
            'price.*'          => 'required|numeric|min:0',
            'unit'             => 'required|array',
            'unit.*'           => 'required|string',
            'item_disc'        => 'nullable|array',
            'item_disc.*'      => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // Generate Return Invoice #
            $lastReturn = \App\Models\PurchaseReturn::latest()->first();
            $nextInvoice = 'RTN-' . str_pad(optional($lastReturn)->id + 1 ?? 1, 5, '0', STR_PAD_LEFT);

            // Create main return record
            $return = \App\Models\PurchaseReturn::create([
                'vendor_id'     => $validated['vendor_id'],
                'warehouse_id'  => $validated['warehouse_id'],
                'return_invoice' => $nextInvoice,
                'return_date'   => $validated['return_date'],
                'return_reason' => $validated['return_reason'] ?? null,
                'bill_amount'   => 0, // calculated below
                'item_discount' => 0,
                'extra_discount' => 0,
                'net_amount'    => 0,
                'paid'          => 0,
                'balance'       => 0,
                'remarks'       => $validated['remarks'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($validated['product_id'] as $index => $productId) {
                $qty   = $validated['qty'][$index];
                $price = $validated['price'][$index];
                $disc  = $validated['item_disc'][$index] ?? 0;
                $unit  = $validated['unit'][$index];
                $lineTotal = ($price * $qty) - $disc;

                \App\Models\PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id'         => $productId,
                    'qty'                => $qty,
                    'price'              => $price,
                    'item_discount'      => $disc,
                    'unit'               => $unit,
                    'line_total'         => $lineTotal,
                ]);

                // Update stock (deduct)
                $stock = \App\Models\Stock::where('branch_id', auth()->id())
                    ->where('warehouse_id', $validated['warehouse_id'])
                    ->where('product_id', $productId)
                    ->first();

                if ($stock) {
                    $stock->qty -= $qty;
                    $stock->save();
                }

                $subtotal += $lineTotal;
            }

            $discount    = $validated['item_disc'] ? array_sum($validated['item_disc']) : 0;
            $extraDisc   = $request->extra_discount ?? 0;
            $netAmount   = ($subtotal - $discount) - $extraDisc;

            $return->update([
                'bill_amount'   => $subtotal,
                'item_discount' => $discount,
                'extra_discount' => $extraDisc,
                'net_amount'    => $netAmount,
                'balance'       => $netAmount,
            ]);

            // Update Vendor Ledger (subtract amount)
            $ledger = \App\Models\VendorLedger::where('vendor_id', $validated['vendor_id'])->first();
            $openingBalance = $ledger ? $ledger->closing_balance : 0;
            $closingBalance = $openingBalance - $netAmount;

            \App\Models\VendorLedger::updateOrCreate(
                ['vendor_id' => $validated['vendor_id']],
                [
                    'admin_or_user_id' => auth()->id(),
                    'opening_balance'  => $openingBalance,
                    'closing_balance'  => $closingBalance,
                    'previous_balance' => $openingBalance,
                ]
            );
        });

        return redirect()->route('purchase.return.index')->with('success', 'Purchase return successfully created.');
    }

    public function purchaseReturnIndex()
    {
        $returns = \App\Models\PurchaseReturn::with(['vendor', 'warehouse'])->latest()->get();
        return view('admin_panel.purchase.purchase_return.index', compact('returns'));
    }
}
