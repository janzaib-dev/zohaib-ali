<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function onhand()
    {
        // Pull every product with its live warehouse stock
        $products = DB::table('products')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units',  'units.id',  '=', 'products.unit_id')
            ->select(
                'products.id',
                'products.item_code',
                'products.item_name',
                'products.size_mode',
                'products.pieces_per_box',
                'products.total_m2',
                'products.price_per_m2',
                'products.sale_price_per_box',
                'products.sale_price_per_piece',
                'products.purchase_price_per_piece',
                'products.purchase_price_per_box',
                DB::raw('COALESCE(brands.name, "-") as brand_name'),
                DB::raw('COALESCE(units.name, "-")  as unit_name')
            )
            ->orderBy('products.item_name')
            ->get();

        $productIds = $products->pluck('id')->toArray();

        // ── Warehouse stock breakdown per product ──────────────────────────
        $whStocks = DB::table('warehouse_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->whereIn('warehouse_stocks.product_id', $productIds)
            ->select(
                'warehouse_stocks.product_id',
                'warehouses.warehouse_name',
                'warehouse_stocks.quantity  as boxes',
                'warehouse_stocks.total_pieces as pieces'
            )
            ->get()
            ->groupBy('product_id');

        // ── Purchase quantities & amounts ──────────────────────────────────
        $purchMap = DB::table('purchase_items')
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty, COALESCE(SUM(line_total),0) as amount')
            ->groupBy('product_id')
            ->get()->keyBy('product_id');

        // ── Sale quantities & amounts ──────────────────────────────────────
        $saleMap = DB::table('sale_items')
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, COALESCE(SUM(qty),0) as qty, COALESCE(SUM(total),0) as amount')
            ->groupBy('product_id')
            ->get()->keyBy('product_id');

        $rows = [];
        $grandOnHand  = 0;
        $grandCostVal = 0;
        $grandSaleVal = 0;
        $outOfStock   = 0;

        foreach ($products as $p) {
            $ppb       = max(1, (int) $p->pieces_per_box);
            $sizeMode  = $p->size_mode ?? 'by_pieces';

            // Live stock from warehouse_stocks
            $whGroup   = $whStocks->get($p->id, collect());
            $totalPcs  = $whGroup->sum('pieces');
            $totalBoxes = $whGroup->sum('boxes');

            // Financials
            $purch  = $purchMap->get($p->id);
            $sale   = $saleMap->get($p->id);
            $pAmt   = (float) ($purch->amount ?? 0);
            $sAmt   = (float) ($sale->amount  ?? 0);

            // Cost & sale value of on-hand stock
            $purchPricePpc = (float) ($p->purchase_price_per_piece ?? 0);
            $purchPricePbx = (float) ($p->purchase_price_per_box   ?? 0);
            $salePricePbx  = (float) ($p->sale_price_per_box       ?? 0);
            $salePricePpc  = (float) ($p->sale_price_per_piece      ?? 0);
            $pricePerM2    = (float) ($p->price_per_m2             ?? 0);
            $totalM2       = (float) ($p->total_m2                 ?? 0);

            // Display qty
            if ($sizeMode === 'by_size') {
                $displayQty = ($totalM2 > 0 && $ppb > 0)
                    ? round($totalPcs * $totalM2, 2) . ' m²'
                    : $totalPcs . ' pcs';
                $costVal = $pricePerM2 > 0
                    ? round($totalPcs * $totalM2, 2) * $pricePerM2
                    : $totalPcs * $purchPricePpc;
                $saleVal = $pricePerM2 > 0
                    ? round($totalPcs * $totalM2, 2) * $pricePerM2
                    : $totalPcs * $salePricePpc;
            } elseif ($sizeMode === 'by_cartons' || $sizeMode === 'by_carton') {
                $boxes = floor($totalPcs / $ppb);
                $loose = $totalPcs % $ppb;
                $displayQty = $boxes . ' box' . ($loose > 0 ? ' + ' . $loose . ' pcs' : '');
                $costVal = $boxes * $purchPricePbx + $loose * $purchPricePpc;
                $saleVal = $boxes * $salePricePbx  + $loose * $salePricePpc;
            } else {
                $displayQty = $totalPcs . ' pcs';
                $costVal = $totalPcs * ($purchPricePpc ?: ($purchPricePbx / $ppb));
                $saleVal = $totalPcs * ($salePricePpc  ?: ($salePricePbx  / $ppb));
            }

            $grandOnHand  += $totalPcs;
            $grandCostVal += $costVal;
            $grandSaleVal += $saleVal;
            if ($totalPcs <= 0) $outOfStock++;

            $rows[] = (object)[
                'id'              => $p->id,
                'item_code'       => $p->item_code ?? '-',
                'item_name'       => $p->item_name ?? '-',
                'brand_name'      => $p->brand_name,
                'unit_name'       => $p->unit_name,
                'size_mode'       => $sizeMode,
                'total_pieces'    => $totalPcs,
                'total_boxes'     => $totalBoxes,
                'display_qty'     => $displayQty,
                'cost_value'      => round($costVal, 2),
                'sale_value'      => round($saleVal, 2),
                'purchase_amount' => round($pAmt, 2),
                'sale_amount'     => round($sAmt, 2),
                'warehouses'      => $whGroup->map(fn($w) => [
                    'name'   => $w->warehouse_name,
                    'boxes'  => $w->boxes,
                    'pieces' => $w->pieces,
                ])->values(),
                'stock_status'    => $totalPcs <= 0 ? 'out' : ($totalPcs < 20 ? 'low' : 'ok'),
            ];
        }

        $summary = (object)[
            'total_products' => count($rows),
            'out_of_stock'   => $outOfStock,
            'low_stock'      => collect($rows)->where('stock_status', 'low')->count(),
            'grand_on_hand'  => $grandOnHand,
            'cost_value'     => round($grandCostVal, 2),
            'sale_value'     => round($grandSaleVal, 2),
        ];

        return view('admin_panel.reporting.onhand', compact('rows', 'summary'));
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
        $warehouseId = $request->warehouse_id;

        $productsQuery = Product::with(['brand', 'category_relation', 'sub_category_relation', 'unit']);
        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }
        $products = $productsQuery->orderBy('item_name')->get();
        $productIds = $products->pluck('id')->toArray();

        // ── Warehouse stocks – all rows for these products ────────────────
        // Each product can appear in MULTIPLE warehouse_stocks rows (one per warehouse)
        $warehouseStocksQuery = DB::table('warehouse_stocks')
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->whereIn('warehouse_stocks.product_id', $productIds)
            ->select(
                'warehouse_stocks.product_id',
                'warehouse_stocks.warehouse_id',
                'warehouses.warehouse_name',
                'warehouse_stocks.quantity as boxes',        // box count
                'warehouse_stocks.total_pieces as pieces',   // total individual pieces
                'warehouse_stocks.remarks'
            );

        if ($warehouseId && $warehouseId !== 'all') {
            $warehouseStocksQuery->where('warehouse_stocks.warehouse_id', $warehouseId);
        }

        // Group by product_id → array of warehouses
        $warehouseStocksGrouped = $warehouseStocksQuery->get()
            ->groupBy('product_id');

        $rows = [];
        $grandTotalValue = 0;
        $grandPurchaseAmt = 0;
        $grandSaleAmt = 0;

        foreach ($products as $product) {

            // ── Initial stock from INIT movements (display only) ──────────
            $initial = (float) DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('ref_type', 'INIT')
                ->sum('qty');

            // ── Purchase (display only) ───────────────────────────────────
            $purchaseData = DB::table('purchase_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty, COALESCE(SUM(line_total),0) as total_amount')
                ->first();
            $purchased = (float) $purchaseData->total_qty;
            $purchaseAmount = (float) $purchaseData->total_amount;

            // ── Sales (display only) ──────────────────────────────────────
            $saleStats = DB::table('sale_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty, COALESCE(SUM(total),0) as total_amount')
                ->first();
            $sold = (float) $saleStats->total_qty;
            $saleAmount = (float) $saleStats->total_amount;

            // ── Returns (display only) ────────────────────────────────────
            $saleReturnQty = (float) DB::table('sale_return_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty')
                ->value('total_qty');

            $purchaseReturnQty = (float) DB::table('purchase_return_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty')
                ->value('total_qty');

            $netPurchased = $purchased - $purchaseReturnQty;
            $netSold = $sold - $saleReturnQty;

            // ── REAL balance = sum of total_pieces in warehouse_stocks ────
            // warehouse_stocks is the live source of truth; it is updated on
            // every purchase receipt, sale, return, and manual adjustment.
            $allWhRows = DB::table('warehouse_stocks')
                ->where('product_id', $product->id)
                ->get();

            $balance = (float) $allWhRows->sum('total_pieces');

            // Fallback for brand-new products with no warehouse_stocks rows
            if ($balance == 0 && $allWhRows->isEmpty()) {
                $balance = $initial + $netPurchased - $netSold;
            }

            // ── Dimensions & size mode ────────────────────────────────────
            $sizeMode = $product->size_mode ?? 'by_piece';
            // Normalize: DB stores 'by_cartons' / 'by_pieces' (with trailing s)
            if ($sizeMode === 'by_cartons') {
                $sizeMode = 'by_carton';
            }
            if ($sizeMode === 'by_pieces') {
                $sizeMode = 'by_piece';
            }

            $height = (float) ($product->height ?? 0);
            $width = (float) ($product->width ?? 0);
            $totalM2Box = (float) ($product->total_m2 ?? ($height * $width / 10000));
            $piecesPerM2 = (float) ($product->pieces_per_m2 ?? 0);
            $piecesPerBox = max(1, (int) ($product->pieces_per_box ?? 1));

            // ── Balance display – depends on size mode ────────────────────
            if ($sizeMode === 'by_size') {
                // by_size  → balance in m². totalM2Box = m² per box
                $balanceBoxes = floor($balance / $piecesPerBox);
                $balancePieces = fmod($balance, $piecesPerBox);
                $totalBalanceM2 = ($piecesPerM2 > 0) ? round($balance / $piecesPerM2, 4) : round($balance * $totalM2Box, 4);
                $displayBalance = [
                    'pieces' => $balance,
                    'boxes' => $balanceBoxes,
                    'loose' => $balancePieces,
                    'total_m2' => $totalBalanceM2,
                    'mode' => 'by_size',
                ];
            } elseif ($sizeMode === 'by_carton') {
                // by_carton → balance shown as boxes + loose pieces
                $balanceBoxes = floor($balance / $piecesPerBox);
                $balancePieces = fmod($balance, $piecesPerBox);
                $displayBalance = [
                    'pieces' => $balance,
                    'boxes' => $balanceBoxes,
                    'loose' => $balancePieces,
                    'mode' => 'by_carton',
                ];
            } else {
                // by_piece  → plain piece count only
                $displayBalance = [
                    'pieces' => $balance,
                    'boxes' => 0,
                    'loose' => $balance,
                    'mode' => 'by_piece',
                ];
            }

            // ── Warehouse breakdown ───────────────────────────────────────
            $whRows = $warehouseStocksGrouped->get($product->id, collect());
            $warehouses = [];
            $whTotalPieces = 0;
            $whTotalBoxes = 0;

            foreach ($whRows as $wh) {
                $whPieces = (int) ($wh->pieces ?? 0);
                $whBoxes = (int) ($wh->boxes ?? 0);

                // Derive display based on size mode
                if ($sizeMode === 'by_carton') {
                    $whDisplay = $whBoxes.' box + '.fmod($whPieces, $piecesPerBox).' pcs';
                } elseif ($sizeMode === 'by_size') {
                    $m2 = ($piecesPerM2 > 0) ? round($whPieces / $piecesPerM2, 4)
                                              : round($whBoxes * $totalM2Box, 4);
                    $whDisplay = $m2.' m²';
                } else {
                    $whDisplay = $whPieces.' pcs';
                }

                $warehouses[] = [
                    'warehouse_id' => $wh->warehouse_id,
                    'warehouse_name' => $wh->warehouse_name,
                    'boxes' => $whBoxes,
                    'pieces' => $whPieces,
                    'display' => $whDisplay,
                    'remarks' => $wh->remarks,
                ];

                $whTotalPieces += $whPieces;
                $whTotalBoxes += $whBoxes;
            }

            // ── Alert status ──────────────────────────────────────────────
            $alertQty = (int) ($product->alert_quantity ?? 0);
            $stockStatus = 'normal';
            if ($balance <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ($alertQty > 0 && $balance <= $alertQty) {
                $stockStatus = 'low_stock';
            }

            // ── Pricing ───────────────────────────────────────────────────
            $salePricePerBox = (float) ($product->sale_price_per_box ?? 0);
            $salePricePerPiece = (float) ($product->sale_price_per_piece ?? 0);
            $purchPricePerBox = (float) ($product->purchase_price_per_box ?? 0);
            $purchPricePerPiece = (float) ($product->purchase_price_per_piece ?? 0);
            $pricePerM2 = (float) ($product->price_per_m2 ?? 0);

            // Stock value
            if ($sizeMode === 'by_size' && $pricePerM2 > 0) {
                $stockValue = ($displayBalance['total_m2'] ?? 0) * $pricePerM2;
            } elseif ($salePricePerPiece > 0) {
                $stockValue = $balance * $salePricePerPiece;
            } else {
                $stockValue = $balance * $salePricePerBox;
            }

            $grandTotalValue += $stockValue;
            $grandPurchaseAmt += $purchaseAmount;
            $grandSaleAmt += $saleAmount;

            $rows[] = [
                'id' => $product->id,
                'item_code' => $product->item_code ?? '-',
                'item_name' => $product->item_name ?? '-',
                'brand' => $product->brand?->name ?? '-',
                'category' => $product->category_relation?->name ?? '-',
                'sub_category' => $product->sub_category_relation?->name ?? '-',
                'unit' => $product->unit?->name ?? '-',
                'color' => $product->color ?? '-',
                // Size / packing
                'size_mode' => $sizeMode,
                'height' => $height,
                'width' => $width,
                'total_m2_box' => $totalM2Box,   // m² per single box
                'pieces_per_m2' => $piecesPerM2,
                'pieces_per_box' => $piecesPerBox,
                // Movements
                'initial_stock' => $initial,
                'purchased' => $purchased,
                'purchase_return_qty' => $purchaseReturnQty,
                'net_purchased' => $netPurchased,
                'sold' => $sold,
                'sale_return_qty' => $saleReturnQty,
                'net_sold' => $netSold,
                // Balance (unified)
                'balance' => $balance,          // always in pieces
                'display_balance' => $displayBalance, // mode-specific
                // Alert
                'alert_quantity' => $alertQty,
                'stock_status' => $stockStatus,
                // Amounts
                'purchase_amount' => $purchaseAmount,
                'sale_amount' => $saleAmount,
                // Pricing
                'sale_price_per_box' => $salePricePerBox,
                'sale_price_per_piece' => $salePricePerPiece,
                'purchase_price_per_box' => $purchPricePerBox,
                'purchase_price_per_piece' => $purchPricePerPiece,
                'price_per_m2' => $pricePerM2,
                'stock_value' => $stockValue,
                // Warehouses (can be 1 or more)
                'warehouses' => $warehouses,
                'wh_total_pieces' => $whTotalPieces,
                'wh_total_boxes' => $whTotalBoxes,
                'wh_count' => count($warehouses),
            ];
        }

        // Also pass list of all warehouses for the filter dropdown
        $allWarehouses = DB::table('warehouses')->select('id', 'warehouse_name')->orderBy('warehouse_name')->get();

        return response()->json([
            'data' => $rows,
            'grand_total' => $grandTotalValue,
            'grand_purchase' => $grandPurchaseAmt,
            'grand_sale' => $grandSaleAmt,
            'warehouses' => $allWarehouses,
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
        $vendorId = $request->vendor_id;
        $status = $request->status;
        $warehouseId = $request->warehouse_id;

        $query = DB::table('purchases')
            ->join('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->leftJoin('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->select(
                'purchases.id',
                'purchases.invoice_no',
                'purchases.purchase_date',
                'purchases.status_purchase',
                'vendors.name as vendor_name',
                'vendors.phone as vendor_phone',
                'warehouses.warehouse_name',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount',
                'purchases.note'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }
        if ($vendorId && $vendorId !== 'all') {
            $query->where('purchases.vendor_id', $vendorId);
        }
        if ($status && $status !== 'all') {
            $query->where('purchases.status_purchase', $status);
        }
        if ($warehouseId && $warehouseId !== 'all') {
            $query->where('purchases.warehouse_id', $warehouseId);
        }

        $purchases = $query->orderBy('purchases.purchase_date', 'desc')->get();

        // Enrich with items and returns
        $purchaseIds = $purchases->pluck('id')->toArray();

        // Items keyed by purchase_id
        $itemsMap = DB::table('purchase_items')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->whereIn('purchase_items.purchase_id', $purchaseIds)
            ->select(
                'purchase_items.purchase_id',
                'products.item_code',
                'products.item_name',
                'purchase_items.qty',
                'purchase_items.unit',
                'purchase_items.price',
                'purchase_items.item_discount',
                'purchase_items.line_total',
                'purchase_items.size_mode',
                'purchase_items.pieces_per_box'
            )
            ->get()
            ->groupBy('purchase_id');

        // Returns keyed by purchase_id
        $returnsMap = DB::table('purchase_returns')
            ->whereIn('purchase_id', $purchaseIds)
            ->select('purchase_id', DB::raw('SUM(net_amount) as total_returned'), DB::raw('COUNT(*) as return_count'))
            ->groupBy('purchase_id')
            ->get()
            ->keyBy('purchase_id');

        $rows = [];
        $grandSubtotal = 0;
        $grandNet = 0;
        $grandPaid = 0;
        $grandDue = 0;
        $grandReturned = 0;

        foreach ($purchases as $p) {
            $items = $itemsMap->get($p->id, collect());
            $returnRow = $returnsMap->get($p->id);
            $totalReturned = $returnRow ? (float) $returnRow->total_returned : 0;

            $grandSubtotal += (float) $p->subtotal;
            $grandNet += (float) $p->net_amount;
            $grandPaid += (float) $p->paid_amount;
            $grandDue += (float) $p->due_amount;
            $grandReturned += $totalReturned;

            $rows[] = [
                'id' => $p->id,
                'invoice_no' => $p->invoice_no ?? ('-'),
                'purchase_date' => $p->purchase_date,
                'status' => $p->status_purchase,
                'vendor_name' => $p->vendor_name,
                'vendor_phone' => $p->vendor_phone ?? '-',
                'warehouse_name' => $p->warehouse_name ?? '-',
                'subtotal' => (float) $p->subtotal,
                'discount' => (float) $p->discount,
                'extra_cost' => (float) $p->extra_cost,
                'net_amount' => (float) $p->net_amount,
                'paid_amount' => (float) $p->paid_amount,
                'due_amount' => (float) $p->due_amount,
                'note' => $p->note ?? '',
                'total_returned' => $totalReturned,
                'return_count' => $returnRow ? (int) $returnRow->return_count : 0,
                'items' => $items->map(fn ($i) => [
                    'item_code' => $i->item_code,
                    'item_name' => $i->item_name,
                    'qty' => $i->qty,
                    'unit' => $i->unit ?? '-',
                    'price' => (float) $i->price,
                    'item_discount' => (float) $i->item_discount,
                    'line_total' => (float) $i->line_total,
                    'size_mode' => $i->size_mode ?? '-',
                ])->values(),
            ];
        }

        // Dropdown data for filters
        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();
        $warehouses = DB::table('warehouses')->select('id', 'warehouse_name')->orderBy('warehouse_name')->get();

        return response()->json([
            'data' => $rows,
            'vendors' => $vendors,
            'warehouses' => $warehouses,
            'grand_subtotal' => $grandSubtotal,
            'grand_net' => $grandNet,
            'grand_paid' => $grandPaid,
            'grand_due' => $grandDue,
            'grand_returned' => $grandReturned,
        ]);
    }

    public function sale_report()
    {
        return view('admin_panel.reporting.sale_report');
    }

    public function fetchsaleReport(Request $request)
    {
        $start = $request->start_date;
        $end = $request->end_date;
        $customerId = $request->customer_id;
        $status = $request->status;
        $warehouseId = $request->warehouse_id;

        // sales table actual columns: total_bill_amount, total_extradiscount, total_net, cash, change
        $query = DB::table('sales')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->select(
                'sales.id',
                'sales.invoice_no',
                'sales.reference',
                'sales.sale_status',
                'sales.total_bill_amount',   // subtotal
                'sales.total_extradiscount', // discount
                'sales.total_net',
                'sales.cash',                // payment received
                'sales.change',              // change given back
                'sales.created_at',
                'customers.customer_name',
                'customers.mobile as customer_phone'
            );

        if ($start && $end) {
            $query->whereBetween(DB::raw('DATE(sales.created_at)'), [$start, $end]);
        }
        if ($customerId && $customerId !== 'all') {
            $query->where('sales.customer_id', $customerId);
        }
        if ($status && $status !== 'all') {
            $query->where('sales.sale_status', $status);
        }
        // Warehouse filter: filter via sale_items
        if ($warehouseId && $warehouseId !== 'all') {
            $query->whereIn('sales.id', function ($sub) use ($warehouseId) {
                $sub->select('sale_id')->from('sale_items')->where('warehouse_id', $warehouseId);
            });
        }

        $sales = $query->orderBy('sales.created_at', 'desc')->get();
        $saleIds = $sales->pluck('id')->toArray();

        // Sale items keyed by sale_id (with warehouse info)
        $itemsMap = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('warehouses', 'sale_items.warehouse_id', '=', 'warehouses.id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select(
                'sale_items.sale_id',
                'products.item_code',
                'products.item_name',
                'sale_items.qty',
                'sale_items.total_pieces',
                'sale_items.price',
                'sale_items.price_per_piece',
                'sale_items.total',
                'sale_items.size_mode',
                'warehouses.warehouse_name'
            )
            ->get()
            ->groupBy('sale_id');

        // Sale returns keyed by sale_id
        $returnsMap = DB::table('sale_returns')
            ->whereIn('sale_id', $saleIds)
            ->select(
                'sale_id',
                DB::raw('SUM(net_amount) as total_returned'),
                DB::raw('COUNT(*) as return_count')
            )
            ->groupBy('sale_id')
            ->get()
            ->keyBy('sale_id');

        $rows = [];
        $grandNet = 0;
        $grandPaid = 0;
        $grandReturned = 0;

        foreach ($sales as $s) {
            $items = $itemsMap->get($s->id, collect());
            $returnRow = $returnsMap->get($s->id);
            $totalReturned = $returnRow ? (float) $returnRow->total_returned : 0;

            // cash is total received, change is refunded — net paid = cash - change
            $cashReceived = (float) ($s->cash ?? 0);
            $changeGiven = (float) ($s->change ?? 0);
            $netPaid = max(0, $cashReceived - $changeGiven);
            $netDue = max(0, (float) $s->total_net - $netPaid);

            $grandNet += (float) $s->total_net;
            $grandPaid += $netPaid;
            $grandReturned += $totalReturned;

            // Derive warehouse name from the first item (since warehouse is on items, not header)
            $warehouseName = $items->first()?->warehouse_name ?? '-';

            $rows[] = [
                'id' => $s->id,
                'invoice_no' => $s->invoice_no ?? ('SLE-'.$s->id),
                'reference' => $s->reference ?? '-',
                'sale_status' => $s->sale_status,
                'customer_name' => $s->customer_name ?? 'Walk-in Customer',
                'customer_phone' => $s->customer_phone ?? '-',
                'warehouse_name' => $warehouseName,
                'subtotal' => (float) ($s->total_bill_amount ?? 0),
                'discount' => (float) ($s->total_extradiscount ?? 0),
                'total_net' => (float) $s->total_net,
                'paid' => $netPaid,
                'due' => $netDue,
                'created_at' => $s->created_at,
                'total_returned' => $totalReturned,
                'return_count' => $returnRow ? (int) $returnRow->return_count : 0,
                'items' => $items->map(fn ($i) => [
                    'item_code' => $i->item_code,
                    'item_name' => $i->item_name,
                    'qty' => $i->qty,
                    'total_pieces' => $i->total_pieces,
                    'price' => (float) $i->price,
                    'price_per_piece' => (float) $i->price_per_piece,
                    'total' => (float) $i->total,
                    'size_mode' => $i->size_mode ?? '-',
                    'warehouse_name' => $i->warehouse_name ?? '-',
                ])->values(),
            ];
        }

        // Dropdown filter data
        $customers = DB::table('customers')->select('id', 'customer_name')->orderBy('customer_name')->get();
        $warehouses = DB::table('warehouses')->select('id', 'warehouse_name')->orderBy('warehouse_name')->get();

        return response()->json([
            'data' => $rows,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'grand_net' => $grandNet,
            'grand_paid' => $grandPaid,
            'grand_due' => $grandNet - $grandPaid,
            'grand_returned' => $grandReturned,
        ]);
    }

    public function customer_ledger_report()
    {
        $customers = DB::table('customers')->select('id', 'customer_name')->get();

        return view('admin_panel.reporting.customer_ledger_report', compact('customers'));
    }

    public function fetch_customer_ledger(Request $request)
    {
        $customerId = (int) $request->customer_id;
        $start      = $request->start_date;
        $end        = $request->end_date;

        $customer = DB::table('customers')->where('id', $customerId)->first();

        if (! $customer || ! $start || ! $end) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        // ── Opening Balance (before start date) ────────────────────────
        // For a CUSTOMER LEDGER we want to show:
        //   DEBIT  = Sale Invoice (customer owes us)
        //   CREDIT = AR Cleared  (customer payment received / receivable cleared)
        // We SKIP all internal double-entry sides: cash/bank, revenue accounts, etc.
        $SKIP_KEYWORDS = [
            'sales revenue',          // Revenue account credit side
            'inventory inward',       // Inventory/COGS
            'vendor payable',         // Vendor side
            'ap cleared',             // Accounts payable
            'cash paid',              // Vendor payment
            'cost of goods',          // COGS
            'cash received',          // Cash/Bank Dr side (internal); the Cr side is 'ar cleared'
            'discount allowed',       // Contra-revenue (keep out of customer statement)
        ];

        $openingEntries = DB::table('journal_entries')
            ->where('party_type', 'App\\Models\\Customer')
            ->where('party_id', $customerId)
            ->where('entry_date', '<', $start)
            ->get();

        $openingBalance = (float) ($customer->opening_balance ?? 0);
        foreach ($openingEntries as $e) {
            $desc = strtolower($e->description ?? '');
            $skip = false;
            foreach ($SKIP_KEYWORDS as $kw) {
                if (str_contains($desc, $kw)) { $skip = true; break; }
            }
            if (!$skip) {
                $openingBalance += ((float)$e->debit - (float)$e->credit);
            }
        }

        // ── Transactions in period ─────────────────────────────────────
        $periodEntries = DB::table('journal_entries')
            ->where('party_type', 'App\\Models\\Customer')
            ->where('party_id', $customerId)
            ->whereBetween('entry_date', [$start, $end])
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = $openingBalance;
        $totalDebit     = 0;
        $totalCredit    = 0;
        $transactions   = [];

        foreach ($periodEntries as $e) {
            $desc = strtolower($e->description ?? '');

            // Skip internal double-entry lines
            $skip = false;
            foreach ($SKIP_KEYWORDS as $kw) {
                if (str_contains($desc, $kw)) { $skip = true; break; }
            }
            if ($skip) continue;

            $dr = (float) $e->debit;
            $cr = (float) $e->credit;
            $runningBalance += ($dr - $cr);
            $totalDebit  += $dr;
            $totalCredit += $cr;

            // Extract ref from description
            $ref = '-';
            if (preg_match('/(SLE-\w+|PO-\w+|RV-\w+|PV-\w+|JV-\w+)/i', $e->description ?? '', $m)) {
                $ref = $m[1];
            }

            // Detect type
            $type = 'journal';
            if (str_contains($desc, 'sale invoice') || str_contains($desc, 'invoice'))          $type = 'sale';
            if (str_contains($desc, 'ar cleared') || str_contains($desc, 'receipt'))             $type = 'receipt';
            if (str_contains($desc, 'return'))                                                    $type = 'return';

            $transactions[] = [
                'date'        => is_string($e->entry_date) ? explode(' ', $e->entry_date)[0] : $e->entry_date,
                'invoice'     => $ref,
                'description' => $e->description ?? '',
                'type'        => $type,
                'debit'       => $dr,
                'credit'      => $cr,
                'balance'     => $runningBalance,
            ];
        }

        return response()->json([
            'customer' => [
                'id'              => $customer->id,
                'customer_id'     => $customer->customer_id   ?? '-',
                'customer_name'   => $customer->customer_name,
                'mobile'          => $customer->mobile        ?? '-',
                'address'         => $customer->address       ?? '-',
                'customer_type'   => $customer->customer_type ?? '-',
                'opening_balance' => $customer->opening_balance ?? 0,
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_debit'     => $totalDebit,
            'total_credit'    => $totalCredit,
            'transactions'    => $transactions,
            'report_period'   => "$start to $end",
        ]);
    }
}
