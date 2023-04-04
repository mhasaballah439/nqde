<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDiscountProductCollection extends Model
{
    use HasFactory;

    protected $table = 'vendor_discount_product_collections';

    protected $guarded = [];
}
