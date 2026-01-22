<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Unit;

use App\Models\StockMovement;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\DNS1D;


class ProductController extends Controller
{


    public function getPrice(Request $request)
    {
        $product = Product::find($request->product_id);

        return response()->json([
            'retail_price' => $product?->price ?? 0
        ]);

    }

    public function productget()
    {
        $products = Product::all();
        return response()->json($products);
    }

    private function upsertStocks(int $productId, float $qtyDelta, int $branchId = 1, int $warehouseId = 1): void
    {
        $updated = Stock::where([
            'branch_id' => $branchId,
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
        ])->update([
            'qty' => DB::raw('qty + ' . ($qtyDelta + 0)),
            'updated_at' => now(),
        ]);

        if (!$updated) {
            Stock::create([
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'qty' => $qtyDelta,
                'reserved_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }





    // ===== Product search (general) =====
    public function searchProducts(Request $request)
    {
        $q = $request->get('q');
        $products = Product::with('brand')
            ->where(function ($query) use ($q) {
                $query->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%")
                    ->orWhere('barcode_path', 'like', "%{$q}%");
            })
            ->get();

        return response()->json($products);
    }

    // ===== List page =====
    public function product()
    {
        $products = Product::with(['category_relation', 'sub_category_relation', 'unit', 'brand'])
            ->when(Auth::user()->email !== "admin@admin.com", function ($query) {
                return $query->where('creater_id', Auth::user()->id);
            })
            ->get();

        $categories = Category::get();
        return view('admin_panel.product.index', compact('products', 'categories'));
    }

    public function productview($id)
    {
        $product = Product::with([
            'category_relation',
            'sub_category_relation',
            'brand',
            'stock'
        ])->find($id);

        return response()->json($product);
    }

////////////////////////


///////////////////////////


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
        $barcodeImage = "data:image/png;base64," . $barcodePNG;

        return response()->json([
            'barcode_number' => $barcodeNumber,
            'barcode_image' => $barcodeImage
        ]);
    }

    // ===== Store product =====
    public function store_product(Request $request)
    {
        if (!Auth::id()) return redirect()->back();

        $userId = Auth::id();

        // Auto item_code
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextCode = $lastProduct ? ('ITEM-' . str_pad($lastProduct->id + 1, 4, '0', STR_PAD_LEFT)) : 'ITEM-0001';

        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = $filename;
        }

        DB::transaction(function () use ($request, $userId, $nextCode, $imagePath) {

            // Create product
            $product = Product::create([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $nextCode,
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'wholesale_price' => $request->wholesale_price,
                'price' => $request->retail_price,
                'alert_quantity' => $request->alert_quantity,
                'model' => $request->model,
                'hs_code' => $request->hs_code,
                'pack_type' => $request->packing_type,
                'pack_qty' => $request->packing_qty,
                'piece_per_pack' => $request->piece_per_pack,
                'loose_piece' => $request->loose_piece,
                'image' => $imagePath,
                'color' => $request->color ? json_encode($request->color) : null,
                'is_part' => 0,
                'is_assembled' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Opening stock → single movement + stocks upsert
            $opening = (float)($request->Stock ?? 0);
            if ($opening > 0) {
                // movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'qty' => $opening,
                    'ref_type' => 'OPENING',
                    'ref_id' => null,
                    'note' => 'Opening stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // stocks (branch/warehouse default if null)
                $this->upsertStocks(
                    productId: $product->id,
                    qtyDelta: $opening,
                    branchId: (int)($request->branch_id ?? 1),
                    warehouseId: (int)($request->warehouse_id ?? 1)
                );
            }


        });

        return redirect()->back()->with('success', 'Product created successfully');
    }


    // ===== Parts search (for BOM modal) with real available qty =====
    //     public function searchPartName(Request $request)
    // {
    //     $q = $request->get('q', '');

    //     $parts = Product::where('is_part', 1)
    //         ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
    //         ->where(function ($x) use ($q) {
    //             $x->where('products.item_name', 'like', "%{$q}%")
    //               ->orWhere('products.item_code', 'like', "%{$q}%");
    //         })
    //         ->groupBy('products.id', 'products.item_name', 'products.item_code', 'products.unit_id')
    //         ->selectRaw('products.id, products.item_name, products.item_code, products.unit_id, COALESCE(SUM(stocks.qty),0) as available_qty')
    //         ->limit(20)
    //         ->get();

    //     return response()->json($parts->map(function ($p) {
    //         return [
    //             'id'            => $p->id,
    //             'item_name'     => $p->item_name,
    //             'item_code'     => $p->item_code,
    //             'unit'          => optional(Unit::find($p->unit_id))->name ?? '',
    //             'available_qty' => (float)$p->available_qty,
    //         ];
    //     }));
    // }



    // ===== Update product =====
    public function update(Request $request, $id)
    {
        $userId = auth()->id();

        // image handle
        $imagePath = Product::where('id', $id)->value('image');
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $imagePath = $imageName; // keep only filename for consistency
        }

        DB::transaction(function () use ($request, $id, $userId, $imagePath) {

            Product::where('id', $id)->update([
                'creater_id' => $userId,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'item_code' => $request->item_code,
                'item_name' => $request->product_name,
                'barcode_path' => $request->barcode_path ?? rand(100000000000, 999999999999),
                'unit_id' => $request->unit,
                'brand_id' => $request->brand_id,
                'wholesale_price' => $request->wholesale_price,
                'price' => $request->retail_price,
                'alert_quantity' => $request->alert_quantity,
                'model' => $request->model,
                'hs_code' => $request->hs_code,
                'pack_type' => $request->packing_type,
                'pack_qty' => $request->packing_qty,
                'piece_per_pack' => $request->piece_per_pack,
                'loose_piece' => $request->loose_piece,
                'image' => $imagePath,
                'is_part' => 0,
                'is_assembled' => 0,
                'updated_at' => now(),
            ]);

            // BOM re-save (replace all for this product)
            DB::table('product_boms')->where('product_id', $id)->delete();

            // Optional: stock adjustment field handle
            if ($request->filled('stock_adjust') && (float)$request->stock_adjust != 0) {
                StockMovement::create([
                    'product_id' => $id,
                    'type' => 'adjustment',
                    'qty' => (float)$request->stock_adjust, // can be negative
                    'ref_type' => 'ADJ',
                    'note' => 'Manual stock adjustment',
                ]);
            }
        });

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
}
