<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'products';

    protected $guarded = [];

    public function category(){
        return $this->belongsTo(ProductCategory::class,'category_id');
    }

    public function additions(){
        return $this->hasMany(ProductAddition::class,'product_id');
    }

    public function scopeActive($q){
        return $q->where('status',1);
    }

    public function tax_group(){
        return $this->belongsTo(TaxGroup::class,'tax_group_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'product_tags','product_id','tag_id');
    }
    public function traits(){
        return $this->hasMany(ProductTrait::class,'product_id');
    }

    public function image()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }

    public function active_branches(){
        return $this->hasMany(ProductBranch::class,'product_id')->where('active',1);
    }

    public function deactive_branches(){
        return $this->hasMany(ProductBranch::class,'product_id')->where('active',1);
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
