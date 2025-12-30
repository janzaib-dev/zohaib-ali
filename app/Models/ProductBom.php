<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBom extends Model
{
     protected $table = 'product_boms';
    
     use HasFactory;
    protected $guarded = [];
    public function product(){ return $this->belongsTo(Product::class,'product_id'); }
    public function part(){ return $this->belongsTo(Product::class,'part_id'); }
}

