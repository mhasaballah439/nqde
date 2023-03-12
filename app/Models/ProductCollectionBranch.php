<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCollectionBranch extends Model
{
    use HasFactory;

    protected $table = 'product_collection_branches';
    protected $guarded = [];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
