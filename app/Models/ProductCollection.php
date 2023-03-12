<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCollection extends Model
{
    use HasFactory;
    protected $table = 'product_collections';

    protected $guarded = [];

    public function branches(){
        return $this->hasMany(ProductCollectionBranch::class,'product_collection_id');
    }
    public function tags(){
        return $this->belongsToMany(Tag::class,'product_collection_tags','product_collection_id','tag_id');
    }
    public function image()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }

    public function products(){
        return $this->hasMany(ProductCollectionProducts::class,'product_collection_id');
    }
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
    public function tax_group(){
        return $this->belongsTo(TaxGroup::class,'tax_group_id');
    }
}
