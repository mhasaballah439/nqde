<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCustody extends Model
{
    use HasFactory;

    protected $table = 'vendor_custodians';

    protected $guarded = [];

    public function employee(){
        return $this->belongsTo(VendorEmployee::class,'employee_id');
    }
}
