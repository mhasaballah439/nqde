<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPromotionTag extends Model
{
    use HasFactory;
    protected $table = 'vendor_promotion_tags';

    protected $guarded = [];
}
