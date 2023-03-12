<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAddition extends Model
{
    use HasFactory;
    protected $table = 'product_additions';
    protected $guarded = [];

    public function addition(){
        return $this->belongsTo(Addition::class,'addition_id');
    }
}
