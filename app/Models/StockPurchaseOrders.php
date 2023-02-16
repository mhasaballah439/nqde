<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchaseOrders extends Model
{
    use HasFactory;

    protected $table = 'stock_purchase_orders';

    protected $guarded = [];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function stock_purchase(){
        return $this->hasOne(StockPurchase::class,'stock_purchase_order_id');
    }
    public function status_name($lang)
    {
        switch ($this->status_id) {
            case 0:
                return __('msg.draft',[],$lang);
            case 1:
                return __('msg.sent',[],$lang);
            case 2:
                return __('msg.closed',[],$lang);
            case 3:
                return __('msg.canceled',[],$lang);
        }
    }
}
