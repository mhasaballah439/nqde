<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCasherDevice extends Model
{
    use HasFactory;
    protected $table = 'vendor_casher_devices';

    protected $guarded = [];
}
