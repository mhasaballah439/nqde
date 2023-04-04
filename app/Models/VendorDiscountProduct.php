<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDiscountProduct extends Model
{
    use HasFactory;

    protected $table = 'vendor_discount_products';

    protected $guarded = [];
}
