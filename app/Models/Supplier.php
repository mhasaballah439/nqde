<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory ,SoftDeletes;
    protected $table = 'suppliers';
    protected $guarded = [];

    public function tags(){
        return $this->belongsToMany(Tag::class,'supplier_tags','supplier_id','tag_id');
    }

    public function stocks(){
        return $this->belongsToMany(Stock::class,'stock_suppliers','supplier_id','stock_id');
    }
}
