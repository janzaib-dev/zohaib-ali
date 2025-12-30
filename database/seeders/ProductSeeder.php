<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample category
        $category = Category::firstOrCreate(['name' => 'Electronics']);
        $subCategory = Subcategory::firstOrCreate([
            'category_id' => $category->id,
            'name' => 'Air-Condition(AC)'
        ]);

        $unit = Unit::firstOrCreate(['name' => 'Piece']);
        $brand = Brand::firstOrCreate(['name' => 'Samsung']); // brand add

        // 🔁 Auto-generate item code
        $lastId  = Product::max('id') ?? 0;
        $nextId  = $lastId + 1;
        $itemCode = 'ITEM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // Sample product
        Product::create([
            'creater_id'     => 1, // replace with real user id if needed
            'category_id'    => $category->id,
            'sub_category_id'=> $subCategory->id,
            'brand_id'       => $brand->id,
            'is_part'        => 0,
            'is_assembled'   => 0,
            'item_code'      => $itemCode,
            'unit_id'        => $unit->id,
            'item_name'      => 'Formal Shirt',
            'color'          => json_encode(['Black']), // example color
            'price'          => 5000,
            'wholesale_price'=> 4500,
            'initial_stock'  => 20,
            'alert_quantity' => 5,
            'barcode_path'   => rand(100000000000, 999999999999),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
