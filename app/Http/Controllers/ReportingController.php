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
            $sold = 0.0;
            $saleAmount = 0.0;

            $sales = DB::table('sales')
                ->select('product_code', 'qty', 'per_total')
                ->whereNotNull('product_code')
                ->get();

            foreach ($sales as $s) {
                $codes = array_map('trim', explode(',', $s->product_code));
                $qtys = array_map('trim', explode(',', $s->qty));
                $totals = array_map('trim', explode(',', $s->per_total));

                foreach ($codes as $idx => $code) {
                    if ($code === $product->item_code && isset($qtys[$idx])) {
                        $sold += floatval($qtys[$idx]);
                        $saleAmount += isset($totals[$idx]) ? floatval($totals[$idx]) : 0;
                    }
                }
            }

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

            $query = DB::table('sales')
                ->leftJoin('customers', 'sales.customer', '=', 'customers.id')
                ->select(
                    'sales.id',
                    'sales.reference',
                    'sales.product',
                    'sales.product_code',
                    'sales.brand',
                    'sales.unit',
                    'sales.per_price',
                    'sales.per_discount',
                    'sales.qty',
                    'sales.per_total',
                    'sales.total_net',
                    'sales.created_at',
                    'customers.customer_name'
                );

            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end]);
            }

            $sales = $query->orderBy('sales.created_at', 'asc')->get();

            // Sale returns merge
            foreach ($sales as $sale) {
                $returns = DB::table('sales_returns')
                    ->where('sale_id', $sale->id)
                    ->get();

                $sale->returns = $returns;
            }

            return response()->json($sales);
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

        // 1. Determine Opening Balance from CustomerLedger (Legacy + V2 Source of Truth)
        $lastEntryBefore = \App\Models\CustomerLedger::where('customer_id', $customerId)
            ->where('created_at', '<', $start)
            ->orderBy('id', 'desc')
            ->first();

        $periodOpeningBalance = $lastEntryBefore
            ? $lastEntryBefore->closing_balance
            : ($customer->opening_balance ?? 0);

        // 2. Fetch Ledger Entries in Range
        $endDateTime = $end." 23:59:59";
        $entries = \App\Models\CustomerLedger::where('customer_id', $customerId)
            ->whereBetween('created_at', [$start, $endDateTime])
            ->orderBy('id', 'asc')
            ->get();

        // 3. Process Entries
        $transactions = $entries->map(function ($row) {
            // Delta Logic for Dr/Cr
            $diff = $row->closing_balance - $row->previous_balance;
            $debit = 0;
            $credit = 0;

            if ($diff > 0.001) {
                $debit = abs($diff);
            } elseif ($diff < -0.001) {
                $credit = abs($diff);
            }

            // Extract Ref/Invoice from Description
            $ref = '-';
            if (preg_match('/Invoice #(\S+)/', $row->description, $matches)) {
                $ref = $matches[1];
            } elseif (preg_match('/Receipt #(\S+)/', $row->description, $matches)) {
                $ref = $matches[1];
            }

            return [
                'date' => $row->created_at->format('Y-m-d H:i'),
                'invoice' => $ref, 
                'description' => $row->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $row->closing_balance,
            ];
        });

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $periodOpeningBalance,
            'transactions' => $transactions,
            'report_period' => "$start to $end",
        ]);
    }
}
