<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDiscountTag extends Model
{
    use HasFactory;

    protected $table = 'vendor_discount_coustomer_tags';
    protected $guarded = [];
}
