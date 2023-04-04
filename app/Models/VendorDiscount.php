<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorDiscount extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'vendor_discounts';

    protected $guarded = [];

    public function branches(){
        return $this->belongsToMany(Branch::class,'venodr_discount_branches','discount_id','branch_id');
    }

    public function products_categories(){
        return $this->belongsToMany(ProductCategory::class,'venodor_discount_product_categories','discount_id','category_id');
    }

    public function products(){
        return $this->belongsToMany(Product::class,'vendor_discount_products','discount_id','product_id');
    }

    public function product_collection(){
        return $this->belongsToMany(ProductCollection::class,'vendor_discount_product_collections','discount_id','product_collection_id');
    }

    public function tags(){
        return $this->hasMany(VendorDiscountTag::class,'discount_id');
    }
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }

    public function getApplyOnNameAttribute(){
        switch ($this->apply_on){
            case 1:
                return __('msg.local');
                case 2:
                return __('msg.safari');
                case 3:
                return __('msg.order_type');
        }
    }

    public function getDiscountTypeNameAttribute(){
        switch ($this->discount_type){
            case 1:
                return __('msg.fixed_price');
                case 2:
                return __('msg.percent');
        }
    }
}
