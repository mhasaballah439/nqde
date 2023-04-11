<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $table = 'order_products';
    protected $guarded = [];

    public function getTotalPriceAttribute(){
        return $this->price * $this->qty;
    }

    public function getTaxPriceAttribute(){

        $tax = $this->total_price * $this->tax/100;
        return (float)$tax;
    }

    public function getFeesDiscountAttribute(){
        $discount = 0;
        if ($this->fees_type == 1)
            $discount = $this->fees_amount;
        elseif ($this->fees_type == 2)
            $discount = $this->sub_total * $this->fees_amount/100;

        return (float)$discount;
    }
    public static function boot()
    {
        parent::boot();

        self::created(function($model){
            $additions_price = 0;
            if ($model->additions_options){
                $additions = $model->additions_options;
                if (!is_array($model->additions_options))
                    $additions = json_decode($model->additions_options);
                if (count($additions) > 0 ){
                    foreach ($additions as $addition_option_id){
                        $ad_data = AdditionOption::fidn($addition_option_id);
                        if ($ad_data)
                            $additions_price+=$ad_data->price;
                    }
                }
            }
            $model->additions_price = $additions_price;
        });
    }
}
