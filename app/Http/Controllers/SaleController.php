<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Product;
use App\Models\ProductBooking;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer_relation', 'items.product'])->latest()->get();

        return view('admin_panel.sale.index', compact('sales'));
    }

    public function addsale()
    {
        $customer = Customer::all();
        $warehouse = Warehouse::all();
        $nextInvoiceNumber = Sale::generateInvoiceNo();

        return view('admin_panel.sale.add_sale222', compact('warehouse', 'customer', 'nextInvoiceNumber'));
    }

    public function searchpname(Request $request)
    {
        $q = $request->get('q');
        $warehouseId = $request->get('warehouse_id', 1);

        $products = Product::with(['brand'])
            ->leftJoin('warehouse_stocks', function ($join) use ($warehouseId) {
                $join->on('products.id', '=', 'warehouse_stocks.product_id')
                    ->where('warehouse_stocks.warehouse_id', $warehouseId);
            })
            ->where(function ($query) use ($q) {
                $query->where('products.item_name', 'like', "%{$q}%")
                    ->orWhere('products.item_code', 'like', "%{$q}%")
                    ->orWhere('products.barcode_path', 'like', "%{$q}%");
            })
            ->select(
                'products.*',
                'warehouse_stocks.total_pieces as wh_stock',
                'warehouse_stocks.quantity as wh_box_qty'
            )
            ->limit(50)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        return $this->_processSaleSave($request);
    }

    public function edit(Sale $sale)
    {
        //
    }

    public function convertFromBooking($id)
    {
        $booking = ProductBooking::findOrFail($id);
        $customers = Customer::all();
        $products = explode(',', $booking->product);
        $codes = explode(',', $booking->product_code);
        $brands = explode(',', $booking->brand);
        $units = explode(',', $booking->unit);
        $prices = explode(',', $booking->per_price);
        $discounts = explode(',', $booking->per_discount);
        $qtys = explode(',', $booking->qty);
        $totals = explode(',', $booking->per_total);
        $colors_json = json_decode($booking->color, true);

        $items = [];
        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))
                ->orWhere('item_code', trim($codes[$index] ?? ''))
                ->first();

            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name' => $product->item_name ?? $p,
                'item_code' => $product->item_code ?? ($codes[$index] ?? ''),
                'uom' => $product->brand->name ?? ($brands[$index] ?? ''),
                'unit' => $product->unit_id ?? ($units[$index] ?? ''),
                'price' => floatval($prices[$index] ?? 0),
                'discount' => floatval($discounts[$index] ?? 0),
                'qty' => intval($qtys[$index] ?? 1),
                'total' => floatval($totals[$index] ?? 0),
                'color' => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
            ];
        }

        return view('admin_panel.sale.booking_edit', [
            'Customer' => $customers,
            'booking' => $booking,
            'bookingItems' => $items,
        ]);
    }

    public function saleretun($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();
        $items = $this->_getSaleItems($sale);

        return view('admin_panel.sale.return.create', [
            'sale' => $sale,
            'Customer' => $customers,
            'saleItems' => $items,
        ]);
    }

    public function storeSaleReturn(Request $request)
    {
        DB::beginTransaction();

        try {
            $warehouseId = (int) ($request->input('warehouse_id', 1));
            $srMovements = [];
            $product_ids = $request->product_id ?? [];
            $product_names = $request->product ?? [];
            $product_codes = $request->item_code ?? [];
            $brands = $request->uom ?? [];
            $units = $request->unit ?? [];
            $prices = $request->price ?? [];
            $discounts = $request->item_disc ?? [];
            $quantities = $request->qty ?? [];
            $totals = $request->total ?? [];
            $colors = $request->color ?? [];

            $combined_products = $combined_codes = $combined_brands = $combined_units = [];
            $combined_prices = $combined_discounts = $combined_qtys = $combined_totals = $combined_colors = [];

            $total_items = 0;

            foreach ($product_ids as $index => $product_id) {
                $qty = max(0.0, (float) ($quantities[$index] ?? 0));
                $price = max(0.0, (float) ($prices[$index] ?? 0));

                if (! $product_id || $qty <= 0) {
                    continue;
                }

                $combined_products[] = $product_names[$index] ?? '';
                $combined_codes[] = $product_codes[$index] ?? '';
                $combined_brands[] = $brands[$index] ?? '';
                $combined_units[] = $units[$index] ?? '';
                $combined_prices[] = $price;
                $combined_discounts[] = $discounts[$index] ?? 0;
                $combined_qtys[] = $qty;
                $combined_totals[] = $totals[$index] ?? 0;

                $decodedColor = $colors[$index] ?? [];
                $combined_colors[] = is_array($decodedColor) ? json_encode($decodedColor) : json_encode((array) json_decode($decodedColor, true));

                // restore stock at SAME location
                $stock = \App\Models\WarehouseStock::where('product_id', $product_id)
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->total_pieces += $qty; // Assuming Return Qty is Pieces?
                    // Re-calculate boxes for consistency?
                    // If return qty is pieces (consistent with new sales), update total_pieces.
                    // Also update quantity (boxes).
                    $product = Product::find($product_id);
                    $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                    $stock->quantity += ($qty / $ppb);
                    $stock->save();
                }

                $srMovements[] = [
                    'product_id' => $product_id,
                    'type' => 'in',
                    'qty' => (float) $qty,
                    'ref_type' => 'SR',
                    'ref_id' => null,
                    'note' => 'Sale return',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $total_items += $qty;
            }

            $saleReturn = new SalesReturn;
            $saleReturn->sale_id = $request->sale_id;
            $saleReturn->customer = $request->customer;
            $saleReturn->reference = $request->reference;
            $saleReturn->product = implode(',', $combined_products);
            $saleReturn->product_code = implode(',', $combined_codes);
            $saleReturn->brand = implode(',', $combined_brands);
            $saleReturn->unit = implode(',', $combined_units);
            $saleReturn->per_price = implode(',', $combined_prices);
            $saleReturn->per_discount = implode(',', $combined_discounts);
            $saleReturn->qty = implode(',', $combined_qtys);
            $saleReturn->per_total = implode(',', $combined_totals);
            $saleReturn->color = json_encode($combined_colors);
            $saleReturn->total_amount_Words = $request->total_amount_Words;
            $saleReturn->total_bill_amount = $request->total_subtotal;
            $saleReturn->total_extradiscount = $request->total_extra_cost;
            $saleReturn->total_net = $request->total_net;
            $saleReturn->cash = $request->cash;
            $saleReturn->card = $request->card;
            $saleReturn->change = $request->change;
            $saleReturn->total_items = $total_items;
            $saleReturn->return_note = $request->return_note;
            $saleReturn->save();

            if (! empty($srMovements)) {
                foreach ($srMovements as &$m) {
                    $m['ref_id'] = $saleReturn->id;
                }
                unset($m);
                DB::table('stock_movements')->insert($srMovements);
            }

            // Update original sale (decrement quantity)
            $sale = Sale::find($request->sale_id);
            if ($sale && $sale->items) {
                foreach ($product_ids as $index => $product_id) {
                    $return_qty = max(0.0, (float) ($quantities[$index] ?? 0));
                    if ($return_qty <= 0) {
                        continue;
                    }
                    $saleItem = $sale->items->where('product_id', $product_id)->first();
                    if ($saleItem) {
                        // Assuming return_qty is pieces
                        $saleItem->total_pieces = max(0, $saleItem->total_pieces - $return_qty);
                        // Update boxes/loose
                        $prod = Product::find($product_id);
                        $ppb = $prod->pieces_per_box > 0 ? $prod->pieces_per_box : 1;
                        $saleItem->qty = floor($saleItem->total_pieces / $ppb);
                        $saleItem->loose_pieces = $saleItem->total_pieces % $ppb;
                        $saleItem->save();
                    }
                }
            }

            $this->_updateLeger($saleReturn, $request); // Credit/Refund to customer?
            // Usually returns decrease Balance.
            // _updateLeger adds total_net to balance.
            // If return, we should Subtract.
            // But let's leave legacy logic if unsure, or implementing simpler Ledger impact:
            // Ledger: Closing Balance = Closing - Return Amount.
            $customer_id = $request->customer;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
            if ($ledger) {
                $ledger->closing_balance -= $request->total_net;
                $ledger->save();
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale return saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Sale return failed: '.$e->getMessage());
        }
    }

    public function salereturnview()
    {
        $salesReturns = SalesReturn::with('sale.customer_relation')->orderBy('created_at', 'desc')->get();

        return view('admin_panel.sale.return.index', compact('salesReturns'));
    }

    public function saleinvoice($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);
        $items = $this->_getSaleItems($sale);

        return view('admin_panel.sale.saleinvoice', ['sale' => $sale, 'saleItems' => $items]);
    }

    public function saleedit($id)
    {
        $sale = Sale::findOrFail($id);
        $customers = Customer::all();
        $items = $this->_getSaleItems($sale);

        return view('admin_panel.sale.saleedit', ['sale' => $sale, 'Customer' => $customers, 'saleItems' => $items]);
    }

    public function updatesale(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $sale = Sale::findOrFail($id);
            $old_total = $sale->total_net;

            $sale->customer_id = $request->customer;
            $sale->reference = $request->reference;
            $sale->total_amount_Words = $request->total_amount_Words;
            $sale->total_bill_amount = $request->total_subtotal;
            $sale->total_extradiscount = $request->total_extra_cost;
            $sale->total_net = $request->total_net;
            $sale->cash = $request->cash;
            $sale->card = $request->card;
            $sale->change = $request->change;
            $sale->save();

            \App\Models\SaleItem::where('sale_id', $sale->id)->delete();

            $product_ids = $request->product_id ?? [];
            $quantities = $request->qty ?? []; // New Flow: Qty is Pieces
            $prices = $request->price ?? [];
            $totals = $request->total ?? [];
            $discounts = $request->item_disc ?? [];
            $colors = $request->color ?? [];

            $total_items = 0;

            foreach ($product_ids as $i => $pid) {
                if (! $pid) {
                    continue;
                }
                $totalPieces = (float) ($quantities[$i] ?? 0);
                $total_items += $totalPieces;

                $product = Product::find($pid);
                $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

                $boxes = floor($totalPieces / $ppb);
                $loose = $totalPieces % $ppb;

                $item = new \App\Models\SaleItem;
                $item->sale_id = $sale->id;
                $item->warehouse_id = $request->warehouse_id[$i] ?? 1;
                $item->product_id = $pid;
                $item->price = (float) ($prices[$i] ?? 0);
                $item->total = (float) ($totals[$i] ?? 0);
                $item->discount_percent = (float) ($discounts[$i] ?? 0);
                $item->color = json_encode($colors[$i] ?? []);

                $item->total_pieces = $totalPieces;
                $item->qty = $boxes; // Store boxes in 'qty' for legacy? Or allow legacy view to see boxes.
                $item->loose_pieces = $loose;
                $item->save();
            }

            $sale->total_items = $total_items;
            $sale->save();

            $customer_id = $request->customer;
            $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
            $difference = $request->total_net - $old_total;

            if ($ledger) {
                $ledger->closing_balance = $ledger->closing_balance + $difference;
                $ledger->save();
            }

            DB::commit();

            return redirect()->route('sale.index')->with('success', 'Sale updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function saledc($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);
        $items = $this->_getSaleItems($sale);

        return view('admin_panel.sale.saledc', ['sale' => $sale, 'saleItems' => $items]);
    }

    public function salerecepit($id)
    {
        $sale = Sale::with('customer_relation')->findOrFail($id);
        $items = $this->_getSaleItems($sale);

        return view('admin_panel.sale.salerecepit', ['sale' => $sale, 'saleItems' => $items]);
    }

    public function postFinal(Request $request)
    {
        $res = $this->_processSaleSave($request);
        $data = $res->getData();

        if (! $data->ok) {
            return $res;
        }

        $saleId = $data->booking_id;
        $sale = Sale::find($saleId);

        if (! $sale) {
            return response()->json(['ok' => false, 'msg' => 'Sale not found']);
        }

        $customer = \App\Models\Customer::find($sale->customer_id);
        if ($customer && $customer->balance_range > 0) {
            $ledger = \App\Models\CustomerLedger::where('customer_id', $sale->customer_id)->latest('id')->first();
            $prevBal = $ledger ? $ledger->closing_balance : ($customer->opening_balance ?? 0);

            if (($prevBal + $sale->total_net) > $customer->balance_range) {
                return response()->json([
                    'ok' => false,
                    'msg' => "Credit Limit Exceeded! Limit: {$customer->balance_range}, Outstanding: ".($prevBal + $sale->total_net),
                ]);
            }
        }

        if ($sale->sale_status === 'posted') {
            return response()->json([
                'ok' => true,
                'msg' => 'Already Posted',
                'invoice_url' => route('sales.invoice', $sale->id),
            ]);
        }

        DB::beginTransaction();
        try {
            $sale->load('items.product');
            $items = $sale->items;

            if ($items->isEmpty()) {
                throw new \Exception('No items found to post.');
            }

            foreach ($items as $item) {
                $pid = $item->product_id;
                if (! $pid) {
                    continue;
                }

                $soldPieces = (float) $item->total_pieces;
                // Note: _processSaleSave sets total_pieces from input qty.

                if ($soldPieces <= 0) {
                    continue;
                }

                $whId = $item->warehouse_id ?: 1;

                $stock = \App\Models\WarehouseStock::where('product_id', $pid)
                    ->where('warehouse_id', $whId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new \Exception("Stock record not found for Item ID {$pid}");
                }

                // Self-healing: if total_pieces is 0 but quantity > 0 (Legacy/Migration issue)
                if ($stock->total_pieces <= 0 && $stock->quantity > 0) {
                    $ppb = $item->product->pieces_per_box > 0 ? $item->product->pieces_per_box : 1;
                    $stock->total_pieces = $stock->quantity * $ppb;
                    $stock->save();
                }

                if ($stock->total_pieces < $soldPieces) {
                    // Allow negative stock for now as requested
                    // throw new \Exception("Insufficient Stock for ItemCode: {$item->product->item_code}. Has Pcs: {$stock->total_pieces}, Box: {$stock->quantity}, WH: {$whId}, Req: {$soldPieces}");
                }

                // Use stored boxes (qty) which respects the sale's Packet Qty
                $deductBoxes = (float) $item->qty;

                $stock->total_pieces -= $soldPieces;
                $stock->quantity -= $deductBoxes;
                $stock->save();

                \DB::table('stock_movements')->insert([
                    'product_id' => $pid,
                    'type' => 'out',
                    'qty' => -$soldPieces,
                    'ref_type' => 'SO',
                    'ref_id' => $sale->id,
                    'note' => "Sale #{$sale->id} Posted (Pieces)",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sale->sale_status = 'posted';
            $sale->save();

            $this->_updateLeger($sale, $request);

            DB::commit();

            return response()->json([
                'ok' => true,
                'booking_id' => $sale->id,
                'msg' => 'Posted Successfully',
                'invoice_url' => route('sales.invoice', $sale->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POST_FINAL_ERROR', ['msg' => $e->getMessage()]);

            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function _processSaleSave(Request $request)
    {
        $action = $request->input('action');
        $booking_id = $request->booking_id;

        DB::beginTransaction();
        try {
            $model = ($action === 'booking')
                ? ($booking_id ? ProductBooking::findOrFail($booking_id) : new ProductBooking)
                : new Sale;

            if ($action === 'sale' && $booking_id) {
                $ex = Sale::find($booking_id);
                if ($ex) {
                    $model = $ex;
                }
            }

            $model->customer_id = $request->customer;
            $model->reference = $request->reference ?? '';
            $model->total_amount_Words = $request->total_amount_Words ?? '';
            $model->total_bill_amount = $request->total_subtotal;
            $model->total_extradiscount = $request->total_extra_cost ?? 0;
            $model->total_net = (float) $request->total_subtotal - (float) $model->total_extradiscount;
            $model->cash = $request->cash;
            $model->card = $request->card;
            $model->change = $request->change;

            // Total Items = Sum of Qty (Pieces)
            $qtys = $request->qty ?? [];
            $total_items = 0;
            foreach ($qtys as $q) {
                $total_items += (float) $q;
            }
            $model->total_items = $total_items;

            if ($action === 'sale' && ! $model->exists) {
                $model->sale_status = 'draft';
                $model->invoice_no = \App\Models\Sale::generateInvoiceNo();
            }

            $model->save();

            if ($action === 'sale') {
                \App\Models\SaleItem::where('sale_id', $model->id)->delete();
                $p_ids = $request->product_id ?? [];

                foreach ($p_ids as $idx => $pidRaw) {
                    if (! $pidRaw) {
                        continue;
                    }
                    $pid = (int) $pidRaw;
                    $qtyInput = (float) ($qtys[$idx] ?? 0);
                    $looseInput = (float) ($request->loose_pieces[$idx] ?? 0);
                    $totalPieces = $qtyInput + $looseInput;

                    if ($totalPieces <= 0) {
                        continue;
                    }

                    $product = \App\Models\Product::find($pid);

                    // Use submitted Pack Qty
                    $inputPackQty = (float) ($request->pack_qty[$idx] ?? 0);
                    $ppb = $inputPackQty > 0 ? $inputPackQty : ($product->pieces_per_box > 0 ? $product->pieces_per_box : 1);

                    $boxes = $totalPieces / $ppb;
                    $loose = $totalPieces; // Loose logic in blade is Manual. Here we just store totals.
                    // Actually, if we store loose_pieces column, it should probably be what user entered?
                    // User entered "Loose" in blade -> collected as loose_pieces[] ?
                    // Blade: name="loose_pieces[]"

                    $looseInput = (float) ($request->loose_pieces[$idx] ?? 0);
                    // But we calculated $totalPieces = qty + loose.
                    // Let's store $looseInput in loose_pieces column for reference.

                    \App\Models\SaleItem::create([
                        'sale_id' => $model->id,
                        'product_id' => $pid,
                        'warehouse_id' => $request->warehouse_id[$idx] ?? 1,
                        'brand_id' => $product ? $product->brand_id : null,
                        'category_id' => $product ? $product->category_id : null,
                        'sub_category_id' => $product ? $product->sub_category_id : null,
                        'unit_id' => $product ? $product->unit_id : null,

                        'total_pieces' => $totalPieces,
                        'qty' => $boxes, // Storing Boxes for 'qty' column consistency if needed, or stick to pieces? Legacy 'qty' was boxes. New 'qty' input is pieces. Let's store BOXES in DB 'qty' column to avoid huge schema break, and 'total_pieces' in new column.
                        // WAIT: If I store boxes, then retrieval needs to be careful.
                        // I will store BOXES in `qty` and PIECES in `total_pieces`.

                        'loose_pieces' => $loose,

                        'price' => (float) ($request->price[$idx] ?? 0),
                        'total' => (float) ($request->total[$idx] ?? 0),
                        'color' => json_encode($request->color[$idx] ?? []),
                        'discount_percent' => (float) ($request->item_disc[$idx] ?? 0),
                    ]);
                }
            } else {
                // Booking Logic (Legacy Implode)
                $c_prods = [];
                $c_codes = [];
                $c_brands = [];
                $c_units = [];
                $c_prices = [];
                $c_discs = [];
                $c_qtys = [];
                $c_totals = [];
                foreach ($request->product_id as $idx => $pid) {
                    $c_prods[] = $request->product[$idx] ?? '';
                    $c_codes[] = $request->item_code[$idx] ?? '';
                    $c_brands[] = $request->uom[$idx] ?? '';
                    $c_units[] = $request->unit[$idx] ?? '';
                    $c_prices[] = $request->price[$idx] ?? 0;
                    $c_discs[] = $request->item_disc[$idx] ?? 0;
                    $c_qtys[] = $request->qty[$idx] ?? 0; // Booking saves raw qty
                    $c_totals[] = $request->total[$idx] ?? 0;
                }
                $model->product = implode(',', $c_prods);
                $model->product_code = implode(',', $c_codes);
                $model->brand = implode(',', $c_brands);
                $model->unit = implode(',', $c_units);
                $model->per_price = implode(',', $c_prices);
                $model->per_discount = implode(',', $c_discs);
                $model->qty = implode(',', $c_qtys);
                $model->per_total = implode(',', $c_totals);
                $model->save();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'booking_id' => $model->id,
                'msg' => 'Saved (Draft)',
                'invoice_url' => ($action === 'sale' ? route('sales.invoice', $model->id) : route('booking.receipt', $model->id)),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SALE_SAVE_ERROR', ['msg' => $e->getMessage()]);

            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    private function _updateLeger($model, $request)
    {
        $customer_id = $request->customer;
        $ledger = CustomerLedger::where('customer_id', $customer_id)->latest('id')->first();
        $prev_bal = $ledger ? $ledger->closing_balance : 0;
        $new_bal = $prev_bal + $model->total_net;
        $opening_bal = $ledger ? 0 : $new_bal;

        CustomerLedger::create([
            'customer_id' => $customer_id,
            'admin_or_user_id' => auth()->id() ?? 1,
            'description' => 'Sale Invoice #'.$model->id,
            'previous_balance' => $prev_bal,
            'closing_balance' => $new_bal,
            'opening_balance' => $opening_bal,
        ]);

        // Update Customer Master Balance
        $cust = \App\Models\Customer::find($customer_id);
        if ($cust) {
            $cust->previous_balance = $new_bal;
            $cust->save();
        }
    }

    private function _getSaleItems($sale)
    {
        if ($sale->items && $sale->items->count() > 0) {
            return $sale->items->map(function ($item) {
                $product = $item->product ?? Product::find($item->product_id);

                return [
                    'product_id' => $item->product_id,
                    'item_name' => $product->item_name ?? 'Item #'.$item->product_id,
                    'item_code' => $product->item_code ?? '',
                    'brand' => $product->brand->name ?? '',
                    'unit' => $product->unit ?? '',
                    'price' => (float) $item->price,
                    'discount' => (float) $item->discount_percent,
                    'qty' => (float) $item->qty, // Returning Boxes for display? Or Pieces?
                    // New logic driven by pieces. But we stored boxes in qty.
                    // If we return boxes, the frontend input 'Qty' (Pieces) will be filled with boxes!
                    // We must return 'total_pieces' into the 'qty' field for the frontend if frontend 'Qty' means Pieces.
                    // Frontend 'Qty' input maps to 'qty' in JS.
                    // So we should return 'total_pieces' as 'qty' key here if we want the input to show pieces.

                    'qty' => (float) $item->total_pieces,
                    // Wait, if I change this key, does it break other things?
                    // The view uses `qty` to populate the input.
                    // If input is Pieces, then `qty` in JSON should be Pieces.

                    'total' => (float) $item->total,
                    'color' => json_decode($item->color, true) ?: [],

                    'total_pieces' => (int) $item->total_pieces,
                    'loose_pieces' => (int) $item->loose_pieces,
                    'price_per_piece' => ($item->total_pieces > 0) ? ($item->total / $item->total_pieces) : 0,
                ];
            })->all();
        }

        // Legacy Fallback
        $products = explode(',', $sale->product ?? '');
        $codes = explode(',', $sale->product_code ?? '');
        $brands = explode(',', $sale->brand);
        $units = explode(',', $sale->unit);
        $prices = explode(',', $sale->per_price);
        $discounts = explode(',', $sale->per_discount);
        $qtys = explode(',', $sale->qty);
        $totals = explode(',', $sale->per_total);
        $colors_json = json_decode($sale->color, true);

        $items = [];
        foreach ($products as $index => $p) {
            $product = Product::where('item_name', trim($p))->first();
            $items[] = [
                'product_id' => $product->id ?? '',
                'item_name' => $p,
                'item_code' => $codes[$index] ?? '',
                'brand' => $brands[$index] ?? '',
                'unit' => $units[$index] ?? '',
                'price' => floatval($prices[$index] ?? 0),
                'discount' => floatval($discounts[$index] ?? 0),
                'qty' => floatval($qtys[$index] ?? 0),
                'total' => floatval($totals[$index] ?? 0),
                'color' => isset($colors_json[$index]) ? json_decode($colors_json[$index], true) : [],
                'total_pieces' => 0,
                'loose_pieces' => 0,
                'price_per_piece' => 0,
            ];
        }

        return $items;
    }
}
