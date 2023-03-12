<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorGiftCard extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'vendor_gift_cards';

    protected $guarded = [];

    public function category(){
        return $this->belongsTo(ProductCategory::class,'category_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'gift_card_tags','gift_card_id','tag_id');
    }

    public function branches(){
        return $this->hasMany(GiftCardBranches::class,'gift_card_id');
    }
    public function municipal_file()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
