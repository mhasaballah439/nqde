<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorTable extends Model
{
    use HasFactory;
    protected $table = 'vendor_tables';
    protected $guarded = [];
}
