<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPromotion extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'vendor_promotions';
    protected $guarded = [];

    public function tags(){
        return $this->belongsToMany(Tag::class,'vendor_promotion_tags','promotion_id','tag_id');
    }

    public function branches(){
        return $this->belongsToMany(Branch::class,'vendor_promotion_branches','promotion_id','branch_id');
    }
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
