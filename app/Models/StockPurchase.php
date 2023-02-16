<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchase extends Model
{
    use HasFactory;

    protected $table = 'stock_purchases';

    protected $guarded = [];

    public function stock_purchase_order(){
        return $this->belongsTo(StockPurchaseOrders::class,'stock_purchase_order_id');
    }
}
