<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function onhand()
    {
        $rows = Product::leftJoin('v_stock_onhand as soh', 'soh.product_id', '=', 'products.id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->selectRaw('
                products.id,
                products.item_code,
                products.item_name,
                COALESCE(brands.name, "") as brand_name,
                COALESCE(units.name, "") as unit_name,
                COALESCE(soh.onhand_qty, 0) as onhand_qty
            ')
            ->orderBy('products.item_name')
            ->get();

        return view('admin_panel.Reporting.onhand', compact('rows'));
    }

    public function item_stock_report()
    {
        $products = Product::orderBy('item_name')->get();

        return view('admin_panel.reporting.item_stock_report', compact('products'));
    }

    // AJAX endpoint to fetch report rows
    public function fetchItemStock(Request $request)
    {
        $productId = $request->product_id;

        $productsQuery = Product::query();
        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }
        $products = $productsQuery->orderBy('item_name')->get();

        $rows = [];
        $grandTotalValue = 0;

        foreach ($products as $product) {
            $initial = (float) ($product->initial_stock ?? 0);

            // Purchased qty & amount
            $purchaseData = DB::table('purchase_items')
                ->where('product_id', $product->id)
                ->select(DB::raw('COALESCE(SUM(qty),0) as total_qty'), DB::raw('COALESCE(SUM(line_total),0) as total_amount'))
                ->first();

            $purchased = (float) $purchaseData->total_qty;
            $purchaseAmount = (float) $purchaseData->total_amount;

            // Sold qty & amount
            $saleStats = DB::table('sale_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty, COALESCE(SUM(total),0) as total_amount')
                ->first();

            $sold = (float) $saleStats->total_qty;
            $saleAmount = (float) $saleStats->total_amount;

            // Actual balance from stocks table
            // Actual balance from v_stock_onhand (consistent with onhand() page)
            $balance = (float) (DB::table('v_stock_onhand')
                ->where('product_id', $product->id)
                ->value('onhand_qty') ?? 0);

            // $balance = $stockRecord ? (float) $stockRecord->qty : 0;

            // Stock value = Balance × Wholesale Price
            $stockValue = $balance * (float) ($product->wholesale_price ?? 0);
            $grandTotalValue += $stockValue;

            $rows[] = [
                'id' => $product->id,
                'item_code' => $product->item_code,
                'item_name' => $product->item_name,
                'initial_stock' => $initial,
                'purchased' => $purchased,
                'purchase_amount' => $purchaseAmount,
                'sold' => $sold,
                'sale_amount' => $saleAmount,
                'balance' => $balance,
                'price' => (float) $product->wholesale_price,
                'stock_value' => $stockValue,
            ];
        }

        return response()->json([
            'data' => $rows,
            'grand_total' => $grandTotalValue,
        ]);
    }

    public function purchase_report()
    {
        return view('admin_panel.reporting.purchase_report');
    }

    public function fetchPurchaseReport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('vendors', 'purchases.vendor_id', '=', 'vendors.id') // join vendor table
            ->select(
                'purchases.purchase_date',
                'purchases.invoice_no',
                'vendors.name as vendor_name', // vendor name
                'products.item_code',
                'products.item_name',
                'purchase_items.qty',
                'purchase_items.unit',
                'purchase_items.price',
                'purchase_items.item_discount',
                'purchase_items.line_total',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }

        $data = $query->orderBy('purchases.purchase_date', 'asc')->get();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function sale_report()
    {
        return view('admin_panel.reporting.sale_report');
    }

    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            // Use Eloquent to handle relations and new table structure
            $query = \App\Models\Sale::with(['customer_relation', 'items.product', 'returns']);

            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
            }

            $sales = $query->orderBy('created_at', 'asc')->get();

            // Transform to match the structure expected by the frontend (CSV strings)
            $transformed = $sales->map(function ($sale) {
                // Construct comma-separated strings for legacy frontend support
                $productNames = $sale->items->map(function ($item) {
                    return $item->product ? $item->product->item_name : 'Unknown';
                })->implode(',');

                // Use SKU or Name as per preference, usually Name for reports
                $productCodes = $sale->items->map(function ($item) {
                    return $item->product ? $item->product->item_code : '-';
                })->implode(',');

                $qtys = $sale->items->pluck('qty')->implode(',');
                $prices = $sale->items->pluck('price')->implode(','); // Unit Price
                $totals = $sale->items->pluck('total')->implode(','); // Line Total

                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference ?? '-',
                    'product' => $productNames,      // Names
                    'product_code' => $productCodes, // Codes
                    'brand' => '-',                  // Could extract from items if needed
                    'unit' => '-',                   // Could extract
                    'per_price' => $prices,
                    'per_discount' => 0,             
                    'qty' => $qtys,
                    'per_total' => $totals,
                    'total_net' => $sale->total_net,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'customer_name' => $sale->customer_relation ? $sale->customer_relation->customer_name : 'Walk-in',
                    'returns' => $sale->returns->map(function($ret) {
                         // Simplify return object for frontend
                         return [
                            'product' => $ret->product ?? '-', // Legacy return might just store string?
                            // New return system uses SalesReturn table. 
                            // If `SalesReturn` model has items relation we need to check.
                            // Assuming `returns` relation on Sale model returns rows from `sales_returns`
                            'qty' => $ret->qty ?? 0,
                            'per_total' => $ret->total_net ?? 0 // best guess based on return table
                         ];
                    })
                ];
            });

            return response()->json($transformed);
        }

        return view('admin_panel.reporting.sale_report');
    }

    public function customer_ledger_report()
    {
        $customers = DB::table('customers')->select('id', 'customer_name')->get();

        return view('admin_panel.reporting.customer_ledger_report', compact('customers'));
    }

    public function fetch_customer_ledger(Request $request)
    {
        $customerId = $request->customer_id;
        $start = $request->start_date;
        $end = $request->end_date;
        
        $customer = DB::table('customers')->where('id', $customerId)->first();

        if (! $customer || ! $start || ! $end) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        // Use BalanceService for proper journal entry based ledger
        $balanceService = app(\App\Services\BalanceService::class);
        $ledgerData = $balanceService->getCustomerLedger($customerId, $start, $end);

        // Format transactions for frontend
        $transactions = collect($ledgerData['transactions'])->map(function ($row) {
            // Extract Ref/Invoice from Description
            $ref = '-';
            if (preg_match('/Invoice #(\S+)/', $row['description'] ?? '', $matches)) {
                $ref = $matches[1];
            } elseif (preg_match('/Receipt #(\S+)/', $row['description'] ?? '', $matches)) {
                $ref = $matches[1];
            }

            return [
                'date' => $row['date'],
                'invoice' => $ref,
                'description' => $row['description'] ?? '',
                'debit' => $row['debit'] ?? 0,
                'credit' => $row['credit'] ?? 0,
                'balance' => $row['balance'] ?? 0,
            ];
        });

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $ledgerData['opening_balance'],
            'closing_balance' => $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'],
            'transactions' => $transactions,
            'report_period' => "$start to $end",
        ]);
    }
}
