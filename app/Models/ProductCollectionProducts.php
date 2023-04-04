<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCollectionProducts extends Model
{
    use HasFactory;
    protected $table = 'product_collection_products';
    protected $guarded = [];
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }

    public function collection(){
        return $this->belongsTo(ProductCollection::class,'product_collection_id');
    }
}
