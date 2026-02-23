<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class ProductController extends Controller
{
    public function getPrice(Request $request)
    {
        $product = Product::find($request->product_id);

        if (! $product) {
            return response()->json(['retail_price' => 0]);
        }

        // Determine price based on mode
        $price = 0;
        if ($product->size_mode === 'by_size') {
            $price = $product->price_per_m2;
        } else {
            // For by_cartons or by_pieces, use the box/piece price
            $price = $product->sale_price_per_box;
        }

        return response()->json([
            'retail_price'          => $price,
            'size_mode'             => $product->size_mode,
            'pieces_per_box'        => $product->pieces_per_box,
            'price_per_m2'          => $product->price_per_m2,
            'sale_price_per_box'    => $product->sale_price_per_box,
            'sale_price_per_piece'  => $product->sale_price_per_piece,
            'height'                => $product->height,
            'width'                 => $product->width,
            'item_code'             => $product->item_code,
        ]);
    }

    public function productget()
    {
        $products = Product::all();

        return response()->json($products);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1, int $warehouseId = 1): void
    {
        $stock = \App\Models\WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            $stock->quantity += $qtyDelta;
            $stock->save();
        } else {
            \App\Models\WarehouseStock::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => $qtyDelta,
                'price' => 0,
            ]);
        }
    }

    // ===== High Performance Select2 Search (Ajax) =====
    public function ajaxSearch(Request $request)
    {
        $term = $request->get('term') ?? $request->get('q') ?? '';

        $query = Product::query()
            ->select('id', 'item_name', 'item_code', 'barcode_path', 'size_mode', 'height', 'width', 'pieces_per_box', 'purchase_price_per_m2', 'purchase_price_per_piece', 'pieces_per_m2')
            ->withSum('warehouseStocks', 'total_pieces') /* Sum PIECES, not boxes */
            ->where(function ($q) use ($term) {
                $q->where('item_name', 'like', "%{$term}%")
                    ->orWhere('item_code', 'like', "%{$term}%")
                    ->orWhere('barcode_path', 'like', "%{$term}%");
            });

        $products = $query->paginate(10); // Lazy loading (10 per request)

        $results = $products->map(function ($p) {
            // Get total pieces from warehouse stocks
            $stockPieces = (float) ($p->warehouse_stocks_sum_total_pieces ?? 0);
            $ppb = $p->pieces_per_box > 0 ? $p->pieces_per_box : 1;

            // Calculate Stock Display (Boxes.Loose vs Pieces)
            $stockDisplay = $stockPieces;
            if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $ppb > 1) {
                // For box-based products, show as "Boxes.Loose"
                $boxes = floor($stockPieces / $ppb);
                $loose = $stockPieces % $ppb;
                $stockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
            }

            return [
                'id' => $p->id,
                'text' => $p->item_name." (SKU: {$p->item_code})", // Enhanced text for selection
                // Custom attributes for template
                'sku' => $p->item_code ?? '',
                'stock' => $stockDisplay,
                'stock_pieces' => $stockPieces, // Raw pieces for validation
                'name' => $p->item_name,
                'size_mode' => $p->size_mode,
                'pieces_per_box' => $ppb,
                'ppb' => $ppb, // Legacy
                'trade_price' => $p->purchase_price_per_piece ?? 0,
                'purchase_price_per_m2' => $p->purchase_price_per_m2 ?? 0,
                'purchase_price_per_piece' => $p->purchase_price_per_piece ?? 0,
                'height' => $p->height ?? 0,
                'length' => $p->height ?? 0, // Alias for purchase snapshot
                'width' => $p->width ?? 0,
                'pieces_per_m2' => $p->pieces_per_m2 ?? 0,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $products->hasMorePages()],
        ]);
    }

    // ===== Product search (general) =====
    public function searchProducts(Request $request)
    {
        $term = $request->get('q', '');

        $products = Product::with('category_relation', 'sub_category_relation', 'brand')
            ->withSum('warehouseStocks', 'total_pieces')
            ->when($term, function ($query) use ($term) {
                $query->where('item_name', 'like', "%{$term}%")
                    ->orWhere('item_code', 'like', "%{$term}%")
                    ->orWhereHas('category_relation', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('sub_category_relation', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('brand', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->limit(500) // limit for performance
            ->get();

        return response()->json($products->map(function ($p, $key) {
            $stockPieces = (float) ($p->warehouse_stocks_sum_total_pieces ?? 0);

            // Calculate Stock Display (Boxes vs Pieces)
            $stockDisplay = $stockPieces;
            $ppb = $p->pieces_per_box > 0 ? $p->pieces_per_box : 1;

            if (($p->size_mode === 'by_cartons' || $p->size_mode === 'by_size') && $p->pieces_per_box > 0) {
                $boxes = floor($stockPieces / $ppb);
                $loose = $stockPieces % $ppb;
                $stockDisplay = $loose > 0 ? "$boxes.$loose" : $boxes;
            }

            return [
                'id' => $p->id,
                'item_code' => $p->item_code,
                'item_name' => $p->item_name,
                'image' => $p->image ? asset('uploads/products/'.$p->image) : null,
                'category_name' => $p->category_relation->name ?? '-',
                'sub_category_name' => $p->sub_category_relation->name ?? '-',
                'height' => $p->height ?? null,
                'width' => $p->width ?? null,
                'pieces_per_box' => $ppb,
                'size_mode' => $p->size_mode,
                'stock' => $stockDisplay,
                'trade_price' => $p->purchase_price_per_piece ?? 0,
                'total_m2' => number_format($p->total_m2 ?? 0, 2),
                'price_per_m2' => number_format($p->price_per_m2 ?? 0, 2),
                'total_price' => number_format($p->total_price ?? 0, 2),
                'brand_name' => $p->brand->name ?? '-',
            ];
        }));
    }

    // ===== List page =====
    public function product()
    {
        $products = Product::with([
            'category_relation',
            'sub_category_relation',
            'unit',
            'brand',
        ])
            ->latest()
            ->paginate(10);

        $categories = Category::get();

        return view('admin_panel.product.index', compact('products', 'categories'));
    }

    public function productview($id)
    {
        $product = Product::with([
            'category_relation',
            'sub_category_relation',
            'brand',
            'unit',
            'warehouseStocks',
        ])->find($id);

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Calculate derived fields
        $totalPieces = $product->warehouseStocks->sum('total_pieces');
        $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;

        $boxes = 0;
        $loose = 0;

        if ($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') {
            $boxes = floor($totalPieces / $ppb);
            $loose = $totalPieces % $ppb;
        } else {
            // For by_pieces, boxes is essentially the piece count if we treat it largely
            // But strict interpretation:
            $boxes = $totalPieces;
            $loose = 0;
        }

        // Append these purely for the view (not saved in DB)
        $product->setAttribute('calculated_total_stock_qty', $totalPieces);
        $product->setAttribute('calculated_boxes_quantity', $boxes);
        $product->setAttribute('calculated_loose_pieces', $loose);

        return response()->json($product);
    }

    // //////////////////////

    // /////////////////////////

    // ===== Create page =====
    public function view_store()
    {
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $brands = Brand::select('id', 'name')->get();

        return view('admin_panel.product.create', compact('categories', 'units', 'brands'));
    }

    // ===== Dependent subcategories =====
    public function getSubcategories($category_id)
    {
        $subcategories = Subcategory::where('category_id', $category_id)->get();

        return response()->json($subcategories);
    }

    // ===== Barcode =====
    public function generateBarcode(Request $request)
    {
        $barcodeNumber = $request->filled('code') ? $request->code : rand(100000000000, 999999999999);
        $barcodePNG = (new DNS1D)->getBarcodePNG($barcodeNumber, 'C39', 3, 50);
        $barcodeImage = 'data:image/png;base64,'.$barcodePNG;

        return response()->json([
            'barcode_number' => $barcodeNumber,
            'barcode_image' => $barcodeImage,
        ]);
    }

    // ===== Store product =====
    // ===== Store product =====
    public function store_product(Request $request)
    {
        if (! Auth::id()) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401)
                : redirect()->route('login');
        }

        // 1. Validate
        $validation = $this->validateProductRequest($request);
        if ($validation->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'errors' => $validation->errors()], 422);
            }

            return redirect()->back()->withErrors($validation)->withInput();
        }

        $mode = $request->size_mode;

        // Initialize variables
        $height = 0;
        $width = 0;
        $piecesPerBox = 0;
        $boxesQuantity = 0;
        $loosePieces = 0;
        $pieceQuantity = 0;

        $totalM2 = 0;
        $totalStockQty = 0;
        $m2PerPiece = 0;
        $piecesPerM2 = 0; // New: How many pieces fit in 1 m²

        // Pricing Vars
        $pricePerM2 = 0;
        $purchasePricePerM2 = 0;

        $salePricePerBox = 0;
        $purchasePricePerPiece = 0;
        $purchasePricePerBox = 0;
        $salePricePerPiece = 0;

        $totalPrice = 0;
        $totalPurchasePrice = 0;

        if ($mode === 'by_size') {
            // By Size Mode
            $height = (float) $request->height;
            $width = (float) $request->width;
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;
            // No loose pieces in by_size usually, but if needed add here

            // Pricing inputs
            $pricePerM2 = (float) $request->price_per_m2;
            $purchasePricePerM2 = (float) $request->purchase_price_per_m2;

            $m2PerPiece = ($height * $width) / 10000;
            $m2PerBox = $m2PerPiece * $piecesPerBox;
            $totalM2 = $m2PerBox; // Storing m2 per box as requested instead of total stock m2

            // Store m2 per piece (0.72) directly as requested, even though column name is pieces_per_m2
            $piecesPerM2 = $m2PerPiece;

            $totalStockQty = $boxesQuantity * $piecesPerBox; // Store in Pieces

            // Prices calculated from m²
            $salePricePerPiece = $m2PerPiece * $pricePerM2;
            $salePricePerBox = $m2PerBox * $pricePerM2;
            $purchasePricePerPiece = $m2PerPiece * $purchasePricePerM2;
            $purchasePricePerBox = $m2PerBox * $purchasePricePerM2;

        } elseif ($mode === 'by_cartons') {
            // By Cartons Mode
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;
            $loosePieces = (int) $request->loose_pieces;

            $totalStockQty = ($piecesPerBox * $boxesQuantity) + $loosePieces;

            $inputSalePc = (float) $request->sale_price_per_box; // Actually per piece input in this mode
            $inputPurchPc = (float) $request->purchase_price_per_piece;

            $salePricePerPiece = $inputSalePc;
            $salePricePerBox = $inputSalePc * $piecesPerBox;

            $purchasePricePerPiece = $inputPurchPc;
            $purchasePricePerBox = $inputPurchPc * $piecesPerBox;

        } elseif ($mode === 'by_pieces') {
            // By Pieces Mode
            $pieceQuantity = (int) $request->piece_quantity;
            $piecesPerBox = 1;
            $boxesQuantity = $pieceQuantity;
            $totalStockQty = $pieceQuantity;

            $inputSalePc = (float) $request->sale_price_per_box;
            $inputPurchPc = (float) $request->purchase_price_per_piece;

            $salePricePerPiece = $inputSalePc;
            $salePricePerBox = $inputSalePc;
            $purchasePricePerPiece = $inputPurchPc;
            $purchasePricePerBox = $inputPurchPc;
        }

        $userId = Auth::id();

        // Item Code from request
        $itemCode = $request->item_code;
        if (empty($itemCode)) {
            $lastProduct = Product::orderBy('id', 'desc')->first();
            $itemCode = $lastProduct ? ('ITEM-' . str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT)) : 'ITEM-0001';
        }

        // Image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        } else {
            $imagePath = null;
        }

        DB::transaction(function () use ($request, $userId, $itemCode, $imagePath, $mode, $height, $width, $piecesPerBox, $boxesQuantity,
            $totalM2, $pricePerM2, $purchasePricePerM2, $totalStockQty, $piecesPerM2,
            $salePricePerPiece, $salePricePerBox, $purchasePricePerPiece, $purchasePricePerBox) {

            // Create product
            $product = Product::create([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $itemCode,
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'model' => $request->model,
                'hs_code' => $request->hs_code,
                'image' => $imagePath,
                'color' => $request->color ? json_encode($request->color) : null,

                // New Fields
                'size_mode' => $mode,
                'height' => $height,
                'width' => $width,
                'pieces_per_box' => $piecesPerBox,
                'pieces_per_m2' => $piecesPerM2,
                // 'boxes_quantity' => $boxesQuantity, // Removed: Not in DB
                // 'loose_pieces' => $loosePieces,     // Removed: Not in DB
                // 'piece_quantity' => $pieceQuantity, // Removed: Not in DB
                // 'total_stock_qty' => $totalStockQty, // Removed: Not in DB

                'total_m2' => $totalM2,

                // Prices
                'price_per_m2' => $pricePerM2,
                'purchase_price_per_m2' => $purchasePricePerM2,

                'sale_price_per_box' => $salePricePerBox,
                'sale_price_per_piece' => $salePricePerPiece,
                'purchase_price_per_piece' => $purchasePricePerPiece,
                'purchase_price_per_box' => $purchasePricePerBox,

                'is_part' => 0,
                'is_assembled' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create Warehouse Stock
            WarehouseStock::create([
                'warehouse_id' => $request->warehouse_id ?? 1, // Default to 1 if not selected
                'product_id' => $product->id,
                'quantity' => $boxesQuantity ?? 0,
                'total_pieces' => $totalStockQty,
                'remarks' => 'Initial Stock',
            ]);

            // Log Stock Movement (Initial)
            if ($totalStockQty > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'qty' => $totalStockQty,
                    'ref_type' => 'INIT',
                    'note' => 'Initial Stock',
                ]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Product created successfully']);
        }

        return redirect()->back()->with('success', 'Product created successfully');
    }

    /*
    // ===== Parts search (for BOM modal) with real available qty =====
        public function searchPartName(Request $request)
    {
        $q = $request->get('q', '');

        $parts = Product::where('is_part', 1)
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->where(function ($x) use ($q) {
                $x->where('products.item_name', 'like', "%{$q}%")
                  ->orWhere('products.item_code', 'like', "%{$q}%");
            })
            ->groupBy('products.id', 'products.item_name', 'products.item_code', 'products.unit_id')
            ->selectRaw('products.id, products.item_name, products.item_code, products.unit_id, COALESCE(SUM(stocks.qty),0) as available_qty')
            ->limit(20)
            ->get();

        return response()->json($parts->map(function ($p) {
            return [
                'id'            => $p->id,
                'item_name'     => $p->item_name,
                'item_code'     => $p->item_code,
                'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
                'available_qty' => (float)$p->available_qty,
            ];
        }));
    }
    */

    // ===== Update product =====
    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        if ($request->wantsJson()) {
            $validation = $this->validateProductRequest($request);
            if ($validation->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validation->errors()], 422);
            }
            $validated = $validation->validated();
        } else {
            $validation = $this->validateProductRequest($request);
            $validation->validate();
        }

        $mode = $request->size_mode;

        // Initialize variables (defaults)
        $height = 0;
        $width = 0;
        $piecesPerBox = 0;
        $boxesQuantity = 0;
        $loosePieces = 0;
        $pieceQuantity = 0;

        $totalM2 = 0;
        $totalStockQty = 0;
        $piecesPerM2 = 0; // New: How many pieces fit in 1 m²

        // Pricing Vars
        $pricePerM2 = 0;        // Sale Price (By Size)
        $purchasePricePerM2 = 0; // Purchase Price (By Size)

        $salePricePerBox = 0;       // Sale Price (Cartons/Pieces)
        $purchasePricePerPiece = 0;   // Purchase Price (Cartons/Pieces)

        $totalPrice = 0;        // Total Sale Price
        $totalPurchasePrice = 0; // Total Purchase Price

        if ($mode === 'by_size') {
            $height = (float) $request->height;
            $width = (float) $request->width;
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;

            // Pricing
            $pricePerM2 = (float) $request->price_per_m2;
            $purchasePricePerM2 = (float) $request->purchase_price_per_m2;

            $m2PerPiece = ($height * $width) / 10000;
            $m2PerBox = $m2PerPiece * $piecesPerBox;
            $totalM2 = $m2PerBox * $boxesQuantity;

            // Calculate pieces per m²
            $piecesPerM2 = $m2PerPiece > 0 ? (1 / $m2PerPiece) : 0;
            $piecesPerM2 = $m2PerPiece > 0 ? (1 / $m2PerPiece) : 0;

            // Logic validation
            if ($totalM2 <= 0) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'errors' => ['total_m2' => ['Total m² cannot be zero.']]], 422);
                }

                return redirect()->back()->withErrors(['total_m2' => 'Total m² cannot be zero.']);
            }

            $totalPrice = $totalM2 * $pricePerM2;
            $totalPurchasePrice = $totalM2 * $purchasePricePerM2;

            // Set total stock qty for by_size
            $totalStockQty = $boxesQuantity;

        } elseif ($mode === 'by_cartons') {
            $piecesPerBox = (int) $request->pieces_per_box;
            $boxesQuantity = (int) $request->boxes_quantity;
            $loosePieces = (int) $request->loose_pieces;

            // Pricing
            $salePricePerBox = (float) $request->sale_price_per_box;
            $purchasePricePerPiece = (float) $request->purchase_price_per_piece;

            $totalStockQty = ($piecesPerBox * $boxesQuantity) + $loosePieces;

            if ($totalStockQty < 1) {
                if ($request->wantsJson()) {
                    return response()->json(['status' => 'error', 'errors' => ['total_stock' => ['Total Stock must be at least 1.']]], 422);
                }

                return redirect()->back()->withErrors(['total_stock' => 'Total Stock must be at least 1.']);
            }

            $totalPrice = $totalStockQty * $salePricePerBox;
            $totalPurchasePrice = $totalStockQty * $purchasePricePerPiece;

        } elseif ($mode === 'by_pieces') {
            $pieceQuantity = (int) $request->piece_quantity;

            // Pricing
            $salePricePerBox = (float) $request->sale_price_per_box;
            $purchasePricePerPiece = (float) $request->purchase_price_per_piece;

            $totalStockQty = $pieceQuantity;

            $totalPrice = $totalStockQty * $salePricePerBox;
            $totalPurchasePrice = $totalStockQty * $purchasePricePerPiece;
        }

        // image handle
        $imagePath = Product::where('id', $id)->value('image');
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $imagePath = $imageName;
        }

        DB::transaction(function () use ($request, $id, $userId, $imagePath, $mode, $height, $width, $piecesPerBox,
            $totalM2, $pricePerM2, $purchasePricePerM2, $salePricePerBox, $purchasePricePerPiece, $piecesPerM2) {

            Product::where('id', $id)->update([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $request->item_code,
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'model' => $request->model,
                'hs_code' => $request->hs_code,
                'image' => $imagePath,

                // New Fields
                'size_mode' => $mode,
                'height' => $height,
                'width' => $width,
                'pieces_per_box' => $piecesPerBox,
                'pieces_per_m2' => $piecesPerM2,
                // 'boxes_quantity' => $boxesQuantity,
                // 'loose_pieces' => $loosePieces,
                // 'piece_quantity' => $pieceQuantity,
                // 'total_stock_qty' => $totalStockQty,

                'total_m2' => $totalM2,

                // Prices
                'price_per_m2' => $pricePerM2,
                'purchase_price_per_m2' => $purchasePricePerM2,

                'sale_price_per_box' => $salePricePerBox,
                'purchase_price_per_piece' => $purchasePricePerPiece,

                // 'total_price' => $totalPrice, // Removed: Not in DB
                // 'total_purchase_price' => $totalPurchasePrice, // Removed: Not in DB

                'is_part' => 0,
                'is_assembled' => 0,
                'updated_at' => now(),
            ]);

            // BOM re-save logic removed as table does not exist
            // DB::table('product_boms')->where('product_id', $id)->delete();

            if ($request->filled('stock_adjust') && (float) $request->stock_adjust != 0) {
                $adjQty = (float) $request->stock_adjust;

                StockMovement::create([
                    'product_id' => $id,
                    'type' => 'adjustment',
                    'qty' => $adjQty,
                    'ref_type' => 'ADJ',
                    'note' => 'Manual stock adjustment',
                ]);

                // Update actual stock
                $this->upsertStocks($id, $adjQty, 1, 1);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Product updated successfully']);
        }

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    // ===== Edit view =====
    public function edit($id)
    {
        $product = Product::with('category_relation', 'sub_category_relation', 'unit', 'brand')->findOrFail($id);
        $categories = Category::all();
        $subcategories = SubCategory::all();
        $brands = Brand::all();

        return view('admin_panel.product.edit', compact('product', 'categories', 'subcategories', 'brands'));
    }

    // ===== Barcode view =====
    public function barcode($id)
    {
        $product = Product::findOrFail($id);

        return view('admin_panel.product.barcode', compact('product'));
    }

    // Shared validation rules
    private function validateProductRequest(Request $request)
    {
        $rules = [
            'product_name' => 'required|string|max:255',
            'category_id' => 'required',
            'item_code' => 'required|string|max:255',
            'sub_category_id' => 'nullable',
            'brand_id' => 'required',
            'unit' => 'nullable',
            'model' => 'nullable', // Made nullable
            'size_mode' => 'required|in:by_size,by_cartons,by_pieces',
        ];

        // Conditional rules logic
        $mode = $request->size_mode;

        if ($mode === 'by_size') {
            $rules = array_merge($rules, [
                'height' => 'required|numeric|gt:0',
                'width' => 'required|numeric|gt:0',
                'pieces_per_box' => 'required|integer|gt:0',
                'boxes_quantity' => 'required|integer|min:0', // Allowed 0 stock
                'price_per_m2' => 'required|numeric|min:0', // Allowed 0 price
                'purchase_price_per_m2' => 'required|numeric|min:0',
            ]);
        } elseif ($mode === 'by_cartons') {
            $rules = array_merge($rules, [
                'pieces_per_box' => 'required|integer|min:1',
                'boxes_quantity' => 'required|integer|min:0',
                'loose_pieces' => 'nullable|integer|min:0',
                'sale_price_per_box' => 'required|numeric|min:0',
                'purchase_price_per_piece' => 'required|numeric|min:0',
            ]);
        } elseif ($mode === 'by_pieces') {
            $rules = array_merge($rules, [
                'piece_quantity' => 'required|integer|min:0', // Allowed 0 stock
                'sale_price_per_box' => 'required|numeric|min:0',
                'purchase_price_per_piece' => 'required|numeric|min:0',
            ]);
        }

        return \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
    }

    // AJAX Validation Endpoint
    public function validateForm(Request $request)
    {
        $validator = $this->validateProductRequest($request);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        return response()->json(['status' => 'success', 'message' => 'Valid']);
    }
}
