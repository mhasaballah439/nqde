<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $guarded = [];

    public function discount_data(){
        return $this->belongsTo(VendorDiscount::class,'discount_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function products(){
        return $this->hasMany(OrderProduct::class,'order_id');
    }

    public function getSubTotalAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0){
            foreach ($this->products as $product)
                $sum+=$product->total_price;
        }
        return (float)$sum;
    }
    public function getSumAdditionsAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0)
            $sum = $this->products()->sum('additions_price');
        return (float)$sum;
    }

    public function getSumCharitiesAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0)
            $sum = $this->products()->sum('charity_price');
        return (float)$sum;
    }

    public function getSumTraitPriceAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0)
            $sum = $this->products()->sum('trait_price');
        return (float)$sum;
    }

    public function getDiscountPriceAttribute(){
        $discount = 0;
        if ($this->discount_type == 1)
            $discount = $this->discount;
        elseif ($this->discount_type == 2)
            $discount = $this->sub_total * $this->discount/100;

        return (float)$discount;
    }
    public function getPromotionDiscountPriceAttribute(){
        $discount = 0;
        if ($this->promotion_type == 1)
            $discount = $this->promotion_discount;
        elseif ($this->promotion_type == 2)
            $discount = $this->sub_total * $this->promotion_discount/100;

        return (float)$discount;
    }
    public function getTemporaryEventDiscountPriceAttribute(){
        $discount = 0;
        if ($this->temporary_event_type == 1)
            $discount = $this->temporary_event_discount;
        elseif ($this->temporary_event_type == 2)
            $discount = $this->sub_total * $this->temporary_event_discount/100;

        return (float)$discount;
    }

    public function getFeesDiscountPriceAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0){
            foreach ($this->products as $product)
                $sum+=$product->fees_discount;
        }
        return (float)$sum;
    }

    public function getCouponDiscountPriceAttribute(){
       $discount = $this->sub_total * $this->coupon_discount/100;
        return (float)$discount;
    }

    public function getSumTaxPriceAttribute(){
        $sum = 0;
        if (isset($this->products) && count($this->products) > 0){
            foreach ($this->products as $product)
                $sum+=$product->tax_price;
        }
        return (float)$sum;
    }

    public function getTotalAttribute(){
       $total = ($this->sub_total + $this->sum_tax_price + $this->sum_trait_price + $this->sum_additions + $this->sum_charities) - ($this->discount_price + $this->gift_card_cost + $this->promotion_discount_price + $this->temporary_event_discount_price + $this->coupon_discount_price + $this->fees_discount_price);
        return (float)$total;
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
        switch ($this->order_type_id){
            case 1:
                return __('msg.delivery');
            case 2:
                return __('msg.inside_place');
        }
    }
}
