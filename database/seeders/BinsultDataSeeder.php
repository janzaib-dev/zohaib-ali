<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BinsultDataSeeder extends Seeder
{
    /**
     * Seed data from binsult1_zohaib-ali.sql dump.
     * Tables: categories, warehouses, products, warehouse_stocks
     *
     * Run with: php artisan db:seed --class=BinsultDataSeeder
     */
    public function run(): void
    {
        $now = Carbon::parse('2026-02-20 12:15:32');

        // -------------------------------------------------------
        // 1. CATEGORIES
        // -------------------------------------------------------
        $this->command->info('Seeding categories...');

        $categories = [
            ['id' => 1, 'name' => 'Electronics',  'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 2, 'name' => 'machine',       'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 3, 'name' => 'Tools',         'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 4, 'name' => 'Plumbing',      'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 5, 'name' => 'Hardware',      'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 6, 'name' => 'Electrical',    'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 7, 'name' => 'Automotive',    'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32'],
            ['id' => 8, 'name' => 'Sanitary',      'created_at' => '2026-02-20 12:19:27', 'updated_at' => '2026-02-20 12:19:27'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(
                ['id' => $cat['id']],
                $cat
            );
        }

        // -------------------------------------------------------
        // 2. WAREHOUSES
        // -------------------------------------------------------
        $this->command->info('Seeding warehouses...');

        $warehouses = [
            ['id' => 1, 'branch_id' => 1, 'warehouse_name' => 'Main Store', 'creater_id' => 1, 'location' => 'Karachi',   'remarks' => 'Main stock storage', 'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32', 'deleted_at' => null],
            ['id' => 2, 'branch_id' => 1, 'warehouse_name' => 'Branch A',   'creater_id' => 1, 'location' => 'Lahore',    'remarks' => 'North region store', 'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32', 'deleted_at' => null],
            ['id' => 3, 'branch_id' => 1, 'warehouse_name' => 'Branch B',   'creater_id' => 1, 'location' => 'Islamabad', 'remarks' => 'Capital branch',     'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32', 'deleted_at' => null],
        ];

        foreach ($warehouses as $warehouse) {
            DB::table('warehouses')->updateOrInsert(
                ['id' => $warehouse['id']],
                $warehouse
            );
        }

        // -------------------------------------------------------
        // 3. PRODUCTS  (Porta sanitary product range)
        // -------------------------------------------------------
        $this->command->info('Seeding products...');

        // brand_id = 2 (Porta) — make sure brand exists
        DB::table('brands')->updateOrInsert(
            ['id' => 2],
            ['id' => 2, 'name' => 'Porta', 'created_at' => '2026-02-20 12:15:32', 'updated_at' => '2026-02-20 12:15:32']
        );

        $productCreatedAt = '2026-02-22 22:09:20';

        $products = [
            [282, '8', '37', 2, 'PORTT01A007',      'A007 White ART Vanity Port',           2749.80,  4628.94],
            [283, '8', '37', 2, 'PORTT01A6111',     'A6111 White ART Vanity (',              4150.17,  7355.96],
            [284, '8', '37', 2, 'PORTT01HDA029',    'HDA029 White ART Vanity',               2401.23,  2934.66],
            [285, '8', '37', 2, 'PORTT01HDA76',     'HDA76 White ART Vanity',                3470.18,  4593.44],
            [286, '8', '37', 2, 'PORTT01HDL505',    'HDL505 White ART Vanity',               4753.38,  6569.04],
            [287, '8', '37', 2, 'PORTT10MT007',     'T007 Black Matt  ART Vanit',             884.76,  1115.19],
            [288, '8', '37', 2, 'PORTT16T007',      'T007 Ivory  ART Vanity',                3831.08,  6844.61],
            [289, '8', '37', 2, 'PORTT01HDL408',    'HD408 White ART Vanity',                2998.31,  5108.00],
            [290, '8', '37', 2, 'PORTT01HDA171',    'HDA171 White ART Vanity',               4468.70,  6729.48],
            [291, '8', '37', 2, 'PORTT01HDA25',     'HDA25 White ART Vanity (',              4184.17,  6429.84],
            [292, '8', '37', 2, 'PORPD01HD11',      'HD11P White (Padestal) (',               989.07,  1376.77],
            [293, '8', '37', 2, 'PORWBPD01HD11',    'HD11 White Basin+Padesta (',             648.39,  1012.62],
            [294, '8', '37', 2, 'PORWBPD16HD11',    'HD11 Ivory Basin+Padesta (',            4971.53,  6322.23],
            [295, '8', '37', 2, 'PORWBPD22HD11',    'HD11 D/Green (DG) B+Pad',              4554.10,  7810.85],
            [296, '8', '37', 2, 'PORWBPD16HD14B',   'HD14 Ivory Basin+Padstal (',            1879.18,  3175.96],
            [297, '8', '37', 2, 'PORWBPD01HD19',    'HD19 White Basin+Padesta (',            3713.60,  5142.44],
            [298, '8', '37', 2, 'PORWBPD01H201A',   'HD201 White Basin+Pad (',               3910.68,  6979.31],
            [299, '8', '37', 2, 'PORWBPD01H203',    'HD203 White B+P (S) ( )',               3558.82,  5205.66],
            [300, '8', '37', 2, 'PORWBPD01HD311',   'HD311 B+P White Small',                 2247.43,  3106.34],
            [301, '8', '37', 2, 'PORWBPD01HD80',    'HD80 White Basin+Pad (  )',               687.96,   882.64],
            [302, '8', '37', 2, 'PORWBPD01HDLP023', 'HDLP023 White Basin+Pad',               3762.69,  5640.98],
            [303, '8', '37', 2, 'PORCTCBHD102',     'HD102 CB 1Pcs Cito D/F',                 740.49,  1177.13],
            [304, '8', '37', 2, 'PORCT01HD044',     'HD044 White (2 Pcs Cito)',               4078.28,  6812.55],
            [305, '8', '37', 2, 'PORCT01HD100',     'HD100 White (1Pcs Cito)D/F',             1381.55,  2338.26],
            [306, '8', '37', 2, 'PORCT01HD101',     'HD101 White (1Pcs Cito)D/F',              683.46,  1075.55],
            [307, '8', '37', 2, 'PORCT01C102DF',    'HD102 Whie 1 Pcs Cito   D/F',           3319.79,  5493.10],
            [308, '8', '37', 2, 'PORCT01HD104',     'HD104 White (1Pcs Cito)D/F',              629.27,   757.68],
            [309, '8', '37', 2, 'PORCT01HD108',     'HD108 White (1Pcs Cito)',                1836.01,  3172.35],
            [310, '8', '37', 2, 'PORCT01HD12',      'HD12 White (2 Pcs Cito)',                3731.68,  5813.81],
            [311, '8', '37', 2, 'PORCT01HD131',     'HD131N White (1 Pcs Cito)',              3621.44,  5842.76],
            [312, '8', '37', 2, 'PORCT01HD173',     'HD173 White (1 Pcs Cito)',               4949.91,  7652.26],
            [313, '8', '37', 2, 'PORCT01HD180A',    'HD180N White 1 Pcs Cito ( )',            4454.54,  7080.11],
            [314, '8', '37', 2, 'PORCT01HD20',      'HD20 White (2 Pcs Cito)',                1267.42,  2178.30],
            [315, '8', '37', 2, 'PORCT01HD200',     'HD200 White (2 Pcs Cito)',               2339.08,  2854.56],
            [316, '8', '37', 2, 'PORCT16HD247..',   'HD247N Ivory Commde W/Hang',             2119.79,  2589.27],
            [317, '8', '37', 2, 'PORCT01HD257',     'HD257N White (2 Pcs Cito)(',             3626.86,  6399.51],
            [318, '8', '37', 2, 'PORCT16HD257',     'HD257N Ivory (2 Pcs Cito)',              4645.43,  7183.07],
            [319, '8', '37', 2, 'PORCT1001MHD427',  'HD427 W/H Black/Wht Mat Commod',         1382.46,  1806.10],
            [320, '8', '37', 2, 'PORCT10MHD427',    'HD427 W/H Black Matt Commode',           3555.76,  5592.74],
            [321, '8', '37', 2, 'PORCTH01HD427',    'HD427 W/Hang White Commode',             4273.46,  7194.99],
            [322, '8', '37', 2, 'PORCTH01HD5025',   'HD5025 W/Hang White Commod',             3657.04,  5919.24],
            [323, '8', '37', 2, 'PORCTH01HD523',    'HD523 W/Hang White Commode',             1180.26,  1964.06],
            [324, '8', '37', 2, 'PORCTH01HD588',    'HD588 W/Hang White Commod',              2991.41,  4151.39],
            [325, '8', '37', 2, 'PORCTH01HD828',    'HD828 W/Hang White Commode',              977.52,  1658.24],
            [326, '8', '37', 2, 'PORCT01HD09',      'HD9N White (2 Pcs Cito)',                4776.84,  7116.29],
            [327, '8', '37', 2, 'PORCT16HD09',      'HD9N Ivory (2 Pcs Cito)',                1437.26,  2102.57],
            [328, '8', '37', 2, 'PORCTH01HD910',    'HD910 W/Hang White Commode',             2394.02,  4126.36],
            [329, '8', '37', 2, 'PORCTH01HD928',    'HD928 W/Hang White Commode',              615.25,   765.21],
            [330, '8', '37', 2, 'POROTHD707',       'HD707 Conceal Orisa Tank',               3393.38,  4672.90],
            [331, '8', '37', 2, 'POROT01HD03',      'HD03 Whit Orisa Tank Porta',             2447.26,  4223.60],
            [332, '8', '37', 2, 'POROT22HD03',      'HD03 D/Green (DG)Orisa Tank A3',         3225.87,  5797.56],
            [333, '8', '37', 2, 'POROT16HD2T',      'HD2T Ivory Orisa Tank H/T',              2325.97,  3559.64],
            [334, '8', '37', 2, 'PORWC22HD13',      'HD13 D/Green (DG)Orisa',                 4336.50,  6712.10],
            [335, '8', '37', 2, 'PORWC01HD43',      'HD43 White Orisa  Porta ( )',             3055.89,  5049.93],
            [336, '8', '37', 2, 'PORWC16HD50',      'HD50 Ivory Orisa Porta',                 1768.92,  2154.33],
            [337, '8', '37', 2, 'PORWC01HD70',      'HD70 White Orisa Porta (  )',             4103.47,  7271.47],
            [338, '8', '37', 2, 'PORWC01HD77',      'HD77 White Orisa Porta (  )',              796.87,  1044.44],
            [339, '8', '37', 2, 'PORWC16HD77',      'HD77 Ivory Orisa Porta (  )',             3134.38,  5639.93],
            [340, '8', '37', 2, 'PORWC01HDD9',      'HDD9 White Orisa  Porta (',              2755.85,  4833.25],
            [341, '8', '37', 2, 'PORWC16HDD9',      'HDD9 Ivory Orisa Porta (',               4300.74,  6343.23],
            [342, '8', '37', 2, 'PORTT01HD03',      'HD03 White Table Top (   )',              2200.60,  3096.76],
            [343, '8', '37', 2, 'PORTT01HD052',     'HD052 White Table Top Porta A3',         2296.67,  4041.12],
            [344, '8', '37', 2, 'PORTT01HD1',       'HD1 White Table Top  (',                 4965.62,  8073.98],
            [345, '8', '37', 2, 'PORTT01HD16',      'HD16 White Table Top (   )',              4601.39,  6422.04],
            [346, '8', '37', 2, 'PORTT03RHD16',     'HD16 CB Table Top  Porta (',             4335.83,  7514.81],
            [347, '8', '37', 2, 'PORTT01HD18',      'HD18 White Table Top  (   )',             1371.13,  2259.96],
            [348, '8', '37', 2, 'PORTT01HD410',     'HD410 White Table Top (   )',             3921.64,  6730.18],
            [349, '8', '37', 2, 'PORTT01HD081',     'HD081 White ART Vanity',                  512.13,   662.81],
            [350, '8', '37', 2, 'PORTT01HD091',     'HD091 White ART Vanity',                 3337.56,  4400.16],
            [351, '8', '37', 2, 'PORTT01HD102.SHOP','HD102 White ART Vanity  Shop',           1800.12,  2400.91],
            [352, '8', '37', 2, 'PORTT01HD102',     'HD102 White ART Vanity',                  949.02,  1152.40],
            [353, '8', '37', 2, 'PORTT01HD103',     'HD103 White ART Vanity',                 3047.39,  4538.42],
            [354, '8', '37', 2, 'PORTT01HD1035',    'HD1035 White ART Vanity',                2782.32,  4615.93],
            [355, '8', '37', 2, 'PORTT01HD105',     'HD105 White ART Vanity',                 4745.69,  5837.15],
            [356, '8', '37', 2, 'PORTT01HD1050',    'HD1050 White ART Vanity',                4771.15,  8548.32],
            [357, '8', '37', 2, 'PORTT01HD1050SHOP','HD1050 White ART Vanity Shop',           1369.51,  1840.41],
            [358, '8', '37', 2, 'PORTT01HD1055',    'HD1055 White ART Vanity',                2762.07,  4216.03],
            [359, '8', '37', 2, 'PORTT01HD106',     'HD106 White ART Vanity',                 3628.97,  5647.14],
            [360, '8', '37', 2, 'PORTT01HD107',     'HD107 White ART Vanity',                 4312.15,  6995.22],
            [361, '8', '37', 2, 'PORTT01HD135',     'HD135 White ART Vanity',                 1309.41,  1724.92],
            [362, '8', '37', 2, 'PORTT01HD705',     'HD705 White ART Vanity',                 2799.13,  4302.92],
            [363, '8', '37', 2, 'PORUC01HD01',      'HD01 White Under Counter (',              540.11,   756.61],
            [364, '8', '37', 2, 'PORUC16HD01',      'HD01 Ivory Under Counter (',             2420.81,  4225.58],
            [365, '8', '37', 2, 'PORUC01HD08',      'HD08 White Under Counter',               2196.72,  2860.26],
            [366, '8', '37', 2, 'PORUC01HDLU22',    'HDLU22 White Under Counter',             1076.53,  1747.03],
            [367, '8', '37', 2, 'PORUC01HD24',      'HD24 White Under Counter (',              973.96,  1224.06],
            [368, '8', '37', 2, 'PORUC01HDLU2',     'HDLU2 White Under Counter',              4057.70,  4965.27],
            [369, '8', '37', 2, 'PORUR01HD400',     'HD400 White Urinal  Porta',              2472.46,  3699.36],
            [370, '8', '37', 2, 'PORUR16HD400',     'HD400 Ivory Urinal  Porta',              1367.18,  2108.32],
            [371, '8', '37', 2, 'PORWBWH10MHD130',  'HD130 Wall Hung Basin M/Black',          2171.19,  2918.04],
        ];

        foreach ($products as [$id, $catId, $subCatId, $brandId, $itemCode, $itemName, $purchasePrice, $salePrice]) {
            DB::table('products')->updateOrInsert(
                ['id' => $id],
                [
                    'id'                         => $id,
                    'creater_id'                 => null,
                    'category_id'                => $catId,
                    'sub_category_id'            => $subCatId,
                    'brand_id'                   => $brandId,
                    'is_part'                    => 0,
                    'is_assembled'               => 0,
                    'item_code'                  => $itemCode,
                    'unit_id'                    => null,
                    'item_name'                  => $itemName,
                    'size_mode'                  => 'by_piece',
                    'height'                     => null,
                    'width'                      => null,
                    'pieces_per_box'             => 0,
                    'pieces_per_m2'              => 0.00,
                    'total_m2'                   => 0.00,
                    'price_per_m2'               => 0.00,
                    'sale_price_per_box'         => 0.00,
                    'purchase_price_per_piece'   => $purchasePrice,
                    'purchase_price_per_box'     => 0.00,
                    'sale_price_per_piece'       => $salePrice,
                    'purchase_price_per_m2'      => 0.00,
                    'color'                      => null,
                    'barcode_path'               => null,
                    'image'                      => null,
                    'model'                      => null,
                    'hs_code'                    => null,
                    'boxes_quantity'             => 0,
                    'loose_pieces'               => 0,
                    'piece_quantity'             => 0,
                    'total_stock_qty'            => 0.00,
                    'created_at'                 => $productCreatedAt,
                    'updated_at'                 => $productCreatedAt,
                    'deleted_at'                 => null,
                ]
            );
        }

        // -------------------------------------------------------
        // 4. WAREHOUSE STOCKS
        // (product_id => quantity mapping from the SQL dump)
        // -------------------------------------------------------
        $this->command->info('Seeding warehouse stocks...');

        $stocks = [
            // [stock_id, warehouse_id, product_id, quantity, total_pieces]
            [104, 1, 282,  3,   3],
            [105, 1, 283,  5,   5],
            [106, 1, 284,  1,   1],
            [107, 1, 285,  7,   7],
            [108, 1, 286,  8,   8],
            [109, 1, 287,  4,   4],
            [110, 1, 288,  1,   1],
            [111, 1, 289,  2,   2],
            [112, 1, 290,  3,   3],
            [113, 1, 291,  2,   2],
            [114, 1, 292,  1,   1],
            [115, 1, 293, 25,  25],
            [116, 1, 294,  4,   4],
            [117, 1, 295,  2,   2],
            [118, 1, 296, 14,  14],
            [119, 1, 297, 15,  15],
            [120, 1, 298,  5,   5],
            [121, 1, 299,  3,   3],
            [122, 1, 300,  4,   4],
            [123, 1, 301, 14,  14],
            [124, 1, 302,  6,   6],
            [125, 1, 303, 50,  50],
            [126, 1, 304, 15,  15],
            [127, 1, 305, 12,  12],
            [128, 1, 306,  6,   6],
            [129, 1, 307, 42,  42],
            [130, 1, 308,  5,   5],
            [131, 1, 309,  3,   3],
            [132, 1, 310, 15,  15],
            [133, 1, 311,  7,   7],
            [134, 1, 312,  1,   1],
            [135, 1, 313,  6,   6],
            [136, 1, 314, 17,  17],
            [137, 1, 315, 17,  17],
            [138, 1, 316,  3,   3],
            [139, 1, 317, 15,  15],
            [140, 1, 318,  3,   3],
            [141, 1, 319,  3,   3],
            [142, 1, 320,  1,   1],
            [143, 1, 321, 22,  22],
            [144, 1, 322,  2,   2],
            [145, 1, 323,  3,   3],
            [146, 1, 324,  4,   4],
            [147, 1, 325,  3,   3],
            [148, 1, 326, 24,  24],
            [149, 1, 327,  1,   1],
            [150, 1, 328,  7,   7],
            [151, 1, 329,  8,   8],
            [152, 1, 330, 90,  90],
            [153, 1, 331,  3,   3],
            [154, 1, 332,  2,   2],
            [155, 1, 333,  3,   3],
            [156, 1, 334,  2,   2],
            [157, 1, 335, 10,  10],
            [158, 1, 336,  2,   2],
            [159, 1, 337, 82,  82],
            [160, 1, 338, 69,  69],
            [161, 1, 339, 28,  28],
            [162, 1, 340, 61,  61],
            [163, 1, 341, 39,  39],
            [164, 1, 342,  9,   9],
            [165, 1, 343,  2,   2],
            [166, 1, 344,  5,   5],
            [167, 1, 345, 25,  25],
            [168, 1, 346, 49,  49],
            [169, 1, 347,  8,   8],
            [170, 1, 348,  9,   9],
            [171, 1, 349,  1,   1],
            [172, 1, 350,  7,   7],
            [173, 1, 351,  1,   1],
            [174, 1, 352,  9,   9],
            [175, 1, 353,  5,   5],
            [176, 1, 354,  3,   3],
            [177, 1, 355,  9,   9],
            [178, 1, 356,  1,   1],
            [179, 1, 357,  1,   1],
            [180, 1, 358,  5,   5],
            [181, 1, 359,  1,   1],
            [182, 1, 360,  3,   3],
            [183, 1, 361,  4,   4],
            [184, 1, 362,  2,   2],
            [185, 1, 363, 27,  27],
            [186, 1, 364,  5,   5],
            [187, 1, 365,  9,   9],
            [188, 1, 366,  2,   2],
            [189, 1, 367,  9,   9],
            [190, 1, 368,  2,   2],
            [191, 1, 369,  3,   3],
            [192, 1, 370,  1,   1],
            [193, 1, 371,  2,   2],
        ];

        foreach ($stocks as [$stockId, $warehouseId, $productId, $quantity, $totalPieces]) {
            DB::table('warehouse_stocks')->updateOrInsert(
                ['id' => $stockId],
                [
                    'id'           => $stockId,
                    'warehouse_id' => $warehouseId,
                    'product_id'   => $productId,
                    'quantity'     => $quantity,
                    'total_pieces' => $totalPieces,
                    'remarks'      => null,
                    'created_at'   => $productCreatedAt,
                    'updated_at'   => $productCreatedAt,
                ]
            );
        }

        $this->command->info('✅ BinsultDataSeeder completed successfully!');
        $this->command->info('   - ' . count($categories)  . ' categories seeded');
        $this->command->info('   - ' . count($warehouses)  . ' warehouses seeded');
        $this->command->info('   - ' . count($products)    . ' products seeded (Porta sanitary range)');
        $this->command->info('   - ' . count($stocks)      . ' warehouse stock entries seeded');
    }
}
