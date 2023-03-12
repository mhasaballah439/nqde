<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCollectionTag extends Model
{
    use HasFactory;

    protected $table = 'product_collection_tags';
    protected $guarded = [];
}
