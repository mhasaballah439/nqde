<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'stocks';
    protected $guarded = [];

    public function store_house(){
        return $this->belongsTo(StoreHouse::class,'stocks');
    }

    public function category(){
        return $this->belongsTo(StockCategory::class,'category_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'stock_tags','stock_id','tag_id');
    }

    public function suppliers(){
        return $this->belongsToMany(Supplier::class,'stock_suppliers','stock_id','supplier_id');
    }
}
