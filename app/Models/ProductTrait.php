<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTrait extends Model
{
    use HasFactory;
    protected $table = 'product_traits';
    protected $guarded = [];
    public function vendor_trait(){
        return $this->belongsTo(VendorTrait::class,'trait_id');
    }
}
