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

        // Prevent identical duplicate submissions within a short timeframe (e.g. user double-clicks or browser form resubmission)
        if (!empty($validated['sale_id'])) {
            $duplicate = SaleReturn::where('sale_id', $validated['sale_id'])
                ->where('created_at', '>=', Carbon::now()->subSeconds(15))
                ->exists();
            if ($duplicate) {
                return redirect()->route('sale.return.index')->with('success', 'Sale return processed successfully. (Duplicate request ignored)');
            }
        }

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

            // Handle Refund Payment – create a Payment Voucher ONLY when amount > 0
            $totalPaid    = 0;
            $pvAccountIds = [];
            $pvAmounts    = [];

            if (! empty($request->payment_account_id)) {
                foreach ($request->payment_account_id as $idx => $accId) {
                    $amt = (float) ($request->payment_amount[$idx] ?? 0);
                    if ($accId && $amt > 0) {
                        $totalPaid    += $amt;
                        $pvAccountIds[] = $accId;
                        $pvAmounts[]    = $amt;
                    }
                }
            }

            if ($totalPaid > 0) {
                // Create legacy Payment Voucher record (payment_vouchers table) because we are paying out cash for the refund.
                $pvid = \App\Models\PaymentVoucher::generateInvoiceNo();
                \App\Models\PaymentVoucher::create([
                    'pvid'             => $pvid,
                    'party_id'         => $validated['customer_id'],
                    'type'             => 'customer',
                    'total_amount'     => $totalPaid,
                    'receipt_date'     => $validated['return_date'],
                    'entry_date'       => $validated['return_date'],
                    'row_account_id'   => json_encode($pvAccountIds),
                    'row_account_head' => json_encode(array_fill(0, count($pvAccountIds), null)),
                    'amount'           => json_encode($pvAmounts),
                    'remarks'          => "Refund for Sale Return #{$nextInvoice}",
                ]);

                // Create V2 VoucherMaster to show in Payment Voucher Index
                $balanceService = app(\App\Services\BalanceService::class);
                $customerControlAccountId = $balanceService->getAccountsReceivableId();
                $v2Lines = [];

                // 1. Debit Customer (Accounts Receivable increases because we refunded cash)
                $v2Lines[] = [
                    'account_id' => $customerControlAccountId,
                    'debit'      => $totalPaid,
                    'credit'     => 0,
                    'narration'  => "Refund for Sale Return #{$nextInvoice}",
                ];

                // 2. Credit Cash/Bank accounts (Cash went out)
                foreach ($pvAccountIds as $idx => $accId) {
                    $amt = $pvAmounts[$idx];
                    if ($amt > 0) {
                        $v2Lines[] = [
                            'account_id' => $accId,
                            'debit'      => 0,
                            'credit'     => $amt,
                            'narration'  => "Refund paid from Account for Sale Return #{$nextInvoice}",
                        ];
                    }
                }

                try {
                    app(\App\Services\VoucherService::class)->createVoucher([
                        'voucher_type' => \App\Models\VoucherMaster::TYPE_PAYMENT,
                        'date'         => $validated['return_date'],
                        'status'       => \App\Models\VoucherMaster::STATUS_POSTED,
                        'party_type'   => \App\Models\Customer::class,
                        'party_id'     => $validated['customer_id'],
                        'remarks'      => "Refund for Sale Return #{$nextInvoice} (Ref: {$pvid})",
                    ], $v2Lines, auth()->id());
                } catch (\Exception $e) {
                    \Log::error('Error creating V2 Voucher for Sale Return: ' . $e->getMessage());
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
                // $return is already saved and has net_amount, so we can sum all returns for this sale
                $totalReturned = \App\Models\SaleReturn::where('sale_id', $sale->id)->sum('net_amount');
                if ($totalReturned >= $sale->total_net && $sale->total_net > 0) {
                    $sale->update(['sale_status' => 'returned']);
                }
            }

            // ─── Journal Entries for Sale Return (Chart of Accounts) ──────────────
            // On a sale return:
            //   Dr Sales Revenue  (revenue decreases — goods came back)
            //   Cr Accounts Receivable  (customer owes us less)
            try {
                $balanceService = app(\App\Services\BalanceService::class);
                $salesAccountId = $balanceService->getSalesRevenueId();
                $arAccountId    = $balanceService->getAccountsReceivableId();
                $returnDate     = $validated['return_date'];

                // Dr Sales Revenue
                \App\Models\JournalEntry::create([
                    'source_type' => \App\Models\SaleReturn::class,
                    'source_id'   => $return->id,
                    'account_id'  => $salesAccountId,
                    'entry_date'  => $returnDate,
                    'debit'       => $netAmount,
                    'credit'      => 0,
                    'description' => "Sale Return #{$nextInvoice} — Revenue Reversal",
                ]);

                // Cr Accounts Receivable
                \App\Models\JournalEntry::create([
                    'source_type' => \App\Models\SaleReturn::class,
                    'source_id'   => $return->id,
                    'account_id'  => $arAccountId,
                    'entry_date'  => $returnDate,
                    'debit'       => 0,
                    'credit'      => $netAmount,
                    'description' => "Sale Return #{$nextInvoice} — AR Reduction",
                    'party_type'  => \App\Models\Customer::class,
                    'party_id'    => $validated['customer_id'],
                ]);

                \Log::info("Sale Return Journal Entries created. Sales Rev debited, AR credited by: {$netAmount}");
            } catch (\Exception $e) {
                \Log::error('Sale Return Journal Entry Error: ' . $e->getMessage());
            }
            // ─────────────────────────────────────────────────────────────────────

            // ─── Update Customer Ledger ────────────────────────────────────────
            // Step 1: Sale Return entry — balance reduces by netAmount (customer owes us less)
            $ledger = \App\Models\CustomerLedger::where('customer_id', $validated['customer_id'])
                ->latest('id')->first();

            $prev_bal = $ledger
                ? (float) $ledger->closing_balance
                : (float) (\App\Models\Customer::find($validated['customer_id'])->previous_balance ?? 0);

            $after_return_bal = $prev_bal - $netAmount;

            \App\Models\CustomerLedger::create([
                'customer_id'      => $validated['customer_id'],
                'admin_or_user_id' => auth()->id() ?? 1,
                'description'      => "Sale Return #{$nextInvoice}",
                'previous_balance' => $prev_bal,
                'closing_balance'  => $after_return_bal,
                'opening_balance'  => 0,
            ]);

            // Step 2: Payment entry — if cash refunded, we paid them back so balance increases (less debt to them)
            $final_bal = $after_return_bal;
            if ($totalPaid > 0) {
                $final_bal = $after_return_bal + $totalPaid;

                \App\Models\CustomerLedger::create([
                    'customer_id'      => $validated['customer_id'],
                    'admin_or_user_id' => auth()->id() ?? 1,
                    'description'      => "Payment Voucher - Refund for Return #{$nextInvoice}",
                    'previous_balance' => $after_return_bal,
                    'closing_balance'  => $final_bal,
                    'opening_balance'  => 0,
                ]);
            }

            // Update Customer master balance
            $cust = \App\Models\Customer::find($validated['customer_id']);
            if ($cust) {
                $cust->previous_balance = $final_bal;
                $cust->save();
            }

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
        $returns = SaleReturn::with(['customer', 'sale', 'warehouse'])->latest()->get();

        // Calculate updated financial details
        $returns->each(function ($return) {
            if ($return->sale) {
                $sale = $return->sale;

                $return->original_net_amount = (float) $sale->total_net;
                $return->original_paid_amount = (float) $sale->cash;

                $totalReturned = SaleReturn::where('sale_id', $sale->id)->sum('net_amount');

                $return->new_net_amount  = max(0, (float) $sale->total_net - $totalReturned);
                $return->new_due_amount  = max(0, (float) $sale->total_net - (float) $sale->cash - $return->net_amount);
                $return->total_returned  = (float) $totalReturned;
            } else {
                $return->original_net_amount = null;
                $return->original_paid_amount = null;
                $return->new_net_amount  = null;
                $return->new_due_amount  = null;
                $return->total_returned  = null;
            }
        });

        return view('admin_panel.sale.sale_return.index', compact('returns'));
    }

    /**
     * View a specific sale return
     */
    public function viewReturn($id)
    {
        $return = SaleReturn::with(['customer', 'warehouse', 'sale', 'items.product.brand', 'items.product.category_relation'])->findOrFail($id);

        return view('admin_panel.sale.sale_return.show', compact('return'));
    }
}
