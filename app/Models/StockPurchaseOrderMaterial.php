<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchaseOrderMaterial extends Model
{
    use HasFactory;

    protected $table = 'stock_purchase_order_materials';

    protected $guarded = [];

    public function stock(){
        return $this->belongsTo(Stock::class,'stock_material_id');
    }
}
