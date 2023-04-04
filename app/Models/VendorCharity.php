<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCharity extends Model
{
    use HasFactory;
    protected $table = 'vendor_charities';
    protected $guarded = [];
}
