<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionStockMaterial extends Model
{
    use HasFactory;

    protected $table = 'production_stock_materials';

    protected $guarded = [];

    public function stock(){
        return $this->belongsTo(Stock::class,'stock_id');
    }
}
