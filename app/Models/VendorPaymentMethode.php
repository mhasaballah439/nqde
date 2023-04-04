<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPaymentMethode extends Model
{
    use HasFactory;
    protected $table = 'vendor_payment_methods';
    protected $guarded = [];
}
