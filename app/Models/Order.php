<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $guarded = [];

    public function discount(){
        return $this->belongsTo(VendorDiscount::class,'discount_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function getStatusNameAttribute(){
        switch ($this->status_id){
            case 1:
                return __('msg.draft');
            case 2:
                return __('msg.closed');
            case 3:
                return __('msg.cancel');
        }
    }

    public function getTypeNameAttribute(){
        switch ($this->status_id){
            case 1:
                return __('msg.delivery');
            case 2:
                return __('msg.inside_place');
        }
    }
}
