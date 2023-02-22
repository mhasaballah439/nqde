<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPlane extends Model
{
    use HasFactory;

    public function plan(){
        return $this->belongsTo(Bouquet::class,'bouquet_id');
    }
}
