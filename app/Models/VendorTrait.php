<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTrait extends Model
{
    use HasFactory;

    protected $table = 'vendor_traits';
    protected $guarded = [];
}
