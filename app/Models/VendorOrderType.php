<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorOrderType extends Model
{
    use HasFactory;
    protected $table = 'vendor_order_types';
    protected $guarded = [];
}
