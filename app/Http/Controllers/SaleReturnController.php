<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function showReturnForm($id)
    {
        $sale = Sale::with(['customer_relation', 'items.product.brand'])->findOrFail($id);
        $accounts = app(\App\Services\BalanceService::class)->getPaymentAccounts();

        // Calculate already returned quantities
        $pastReturns = SaleReturn::where('sale_id', $id)
            ->with('items')
            ->get();

        $returnedQtyMap = [];
        foreach ($pastReturns as $sr) {
            foreach ($sr->items as $srItem) {
                if (! isset($returnedQtyMap[$srItem->product_id])) {
                    $returnedQtyMap[$srItem->product_id] = 0;
                }
                $returnedQtyMap[$srItem->product_id] += $srItem->qty;
            }
        }

        // Format sale items with complete product data
        $sale->items->each(function ($item) use ($returnedQtyMap) {
            $product = $item->product;
            $alreadyReturned = $returnedQtyMap[$item->product_id] ?? 0;

            // Add product details
            $item->item_name = $product->product_name ?? $product->item_name ?? 'Unknown';
            $item->item_code = $product->product_code ?? $product->item_code ?? '';

            // Fix brand - get name from relationship
            if ($product->brand && is_object($product->brand)) {
                $item->brand = $product->brand->name ?? '';
            } else {
                $item->brand = $product->brand_name ?? '';
            }

            // Ensure pieces_per_box is numeric and valid
            $item->pieces_per_box = (int) ($product->pieces_per_box ?? $product->packet_size ?? 1);
            if ($item->pieces_per_box <= 0) {
                $item->pieces_per_box = 1;
            }

            $item->size_mode = $product->size_mode ?? 'by_pieces';
            $item->pieces_per_m2 = $product->m2_of_box ?? 0;
            $item->unit = $item->unit ?? 'pc';

            // Quantity calculations
            $item->qty = $item->total_pieces ?? $item->qty ?? 0;
            $item->original_qty = $item->qty;
            $item->returned_qty = $alreadyReturned;
            $item->max_returnable = max(0, $item->qty - $alreadyReturned);

            // Pricing (use sale price, not purchase price)
            $item->price = $item->price ?? $item->per_price ?? 0;
            $item->discount = $item->discount ?? $item->per_discount ?? 0;
        });

        return view('admin_panel.sale.sale_return.create', compact('sale', 'accounts', 'returnedQtyMap'));
    }

    /**
     * Process the sale return
     */
    public function processSaleReturn(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'nullable|exists:sales,id',
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0',
            'price' => 'required|array',
            'price.*' => 'required|numeric|min:0',
            'item_discount' => 'nullable|array',
            'extra_discount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
            'payment_account_id' => 'nullable|array',
            'payment_amount' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // Generate Return Invoice Number
            $lastReturn = SaleReturn::orderBy('id', 'desc')->first();
            $nextInvoice = $lastReturn
                ? 'SR-'.str_pad((int) str_replace('SR-', '', $lastReturn->return_invoice) + 1, 4, '0', STR_PAD_LEFT)
                : 'SR-0001';

            // Create Sale Return Header
            $return = SaleReturn::create([
                'sale_id' => $validated['sale_id'] ?? null,
                'return_invoice' => $nextInvoice,
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'return_date' => $validated['return_date'],
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'posted',
            ]);

            $sale = $validated['sale_id'] ? Sale::find($validated['sale_id']) : null;
            $now = Carbon::now();
            $movements = [];
            $subtotal = 0;
            $totalItemDiscount = 0;

            // Process Each Return Item
            foreach ($request->product_id as $idx => $productId) {
                $qty = (float) $request->qty[$idx]; // Total pieces
                if ($qty <= 0) {
                    continue;
                }

                $price = (float) $request->price[$idx];
                $itemDisc = (float) ($request->item_discount[$idx] ?? 0);
                $lineTotal = ($qty * $price) - $itemDisc;

                // Get product for PPB calculation
                $product = Product::find($productId);
                $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

                // Calculate boxes and loose pieces
                $boxes = floor($qty / $ppb);
                $loosePieces = $qty % $ppb;

                // Create Return Item
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id' => $productId,
                    'warehouse_id' => $validated['warehouse_id'],
                    'qty' => $qty,
                    'boxes' => $boxes + ($loosePieces / $ppb), // Decimal boxes
                    'loose_pieces' => $loosePieces,
                    'price' => $price,
                    'item_discount' => $itemDisc,
                    'unit' => 'pc',
                    'line_total' => $lineTotal,
                ]);

                // Update Stock (INCREMENT - goods coming back)
                $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    // Robust calculation
                    $currentTotalPieces = $stock->quantity * $ppb;
                    $newTotalPieces = $currentTotalPieces + $qty;

                    $stock->total_pieces = $newTotalPieces;
                    $stock->quantity = $newTotalPieces / $ppb;
                    $stock->save();
                } else {
                    // Create new stock entry
                    WarehouseStock::create([
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_id' => $productId,
                        'total_pieces' => $qty,
                        'quantity' => $qty / $ppb,
                        'price' => 0,
                    ]);
                }

                // Stock Movement (IN - goods returned to warehouse)
                $movements[] = [
                    'product_id' => $productId,
                    'type' => 'in',
                    'qty' => $qty,
                    'ref_type' => 'SALE_RETURN',
                    'ref_id' => $return->id,
                    'note' => "Return #{$nextInvoice}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $subtotal += $lineTotal;
                $totalItemDiscount += $itemDisc;
            }

            // Bulk Insert Stock Movements
            if (! empty($movements)) {
                DB::table('stock_movements')->insert($movements);
            }

            $netAmount = ($subtotal - $totalItemDiscount) - ($request->extra_discount ?? 0);

            // Handle Refund Payment (Payment Voucher)
            $totalPaid = 0;
            if (! empty($request->payment_account_id)) {
                $voucherService = app(\App\Services\VoucherService::class);
                $arId = app(\App\Services\BalanceService::class)->getAccountsReceivableId();

                foreach ($request->payment_account_id as $idx => $accId) {
                    $amt = (float) ($request->payment_amount[$idx] ?? 0);
                    if ($accId && $amt > 0) {
                        $totalPaid += $amt;

                        // Create Payment Voucher via VoucherService (auto-generates voucher_no + posts to journal)
                        $voucherService->createVoucher(
                            [
                                'voucher_type' => \App\Models\VoucherMaster::TYPE_PAYMENT,
                                'date' => $validated['return_date'],
                                'status' => 'posted',
                                'party_type' => \App\Models\Customer::class,
                                'party_id' => $validated['customer_id'],
                                'remarks' => "Refund for Return #{$nextInvoice}",
                            ],
                            [
                                // Cr Cash / Bank account (money goes out)
                                [
                                    'account_id' => $accId,
                                    'debit' => 0,
                                    'credit' => $amt,
                                    'narration' => "Cash Refund for Return #{$nextInvoice}",
                                ],
                                // Dr Accounts Receivable (reduces what customer owes)
                                [
                                    'account_id' => $arId,
                                    'debit' => $amt,
                                    'credit' => 0,
                                    'narration' => "Refund to Customer - Return #{$nextInvoice}",
                                ],
                            ]
                        );
                    }
                }
            }

            // Update Return Totals
            $return->update([
                'bill_amount' => $subtotal,
                'item_discount' => $totalItemDiscount,
                'net_amount' => $netAmount,
                'paid' => $totalPaid,
                'balance' => $netAmount - $totalPaid,
            ]);

            // Update Sale Status (if full return)
            if ($sale) {
                $sale->update(['sale_status' => 'returned']);
            }

            // Create Journal Voucher (Credit Note)
            $transactionService = app(\App\Services\TransactionService::class);
            if (method_exists($transactionService, 'createSaleReturnVoucher')) {
                $transactionService->createSaleReturnVoucher($return);
            }

            // Update Customer Ledger (if exists)
            // Sale Return increases customer balance (they owe less or we owe them)
            $balanceChange = $netAmount - $totalPaid;

            DB::commit();

            return redirect()->route('sale.return.index')->with('success', 'Sale return processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error processing return: '.$e->getMessage());
        }
    }

    /**
     * Display all sale returns
     */
    public function saleReturnIndex()
    {
        $returns = SaleReturn::with(['customer', 'sale'])->latest()->get();

        // Calculate updated financial details
        $returns->each(function ($return) {
            if ($return->sale) {
                $sale = $return->sale;

                $return->original_net_amount = $sale->total_net;

                $totalReturned = SaleReturn::where('sale_id', $sale->id)
                    ->sum('net_amount');

                $return->new_net_amount = max(0, $sale->total_net - $totalReturned);
                $return->total_returned = $totalReturned;
            }
        });

        return view('admin_panel.sale.sale_return.index', compact('returns'));
    }

    /**
     * View a specific sale return
     */
    public function viewReturn($id)
    {
        $return = SaleReturn::with(['customer', 'sale', 'items.product'])->findOrFail($id);

        return view('admin_panel.sale.sale_return.show', compact('return'));
    }
}
