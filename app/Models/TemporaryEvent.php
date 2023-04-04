<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryEvent extends Model
{
    use HasFactory;
    protected $table = 'temporary_events';
    protected $guarded = [];
    public function branches(){
        return $this->belongsToMany(Branch::class,'temporary_events_branches','event_id','branch_id');
    }

    public function products_categories(){
        return $this->belongsToMany(ProductCategory::class,'temporary_events_categories','event_id','category_id');
    }

    public function products(){
        return $this->belongsToMany(Product::class,'temporary_events_products','event_id','product_id');
    }

    public function product_collection(){
        return $this->belongsToMany(ProductCollection::class,'temporary_events_collections','event_id','collection_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'temporary_events_tags','event_id','tag_id');
    }
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
