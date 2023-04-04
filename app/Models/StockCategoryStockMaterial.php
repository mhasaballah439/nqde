<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCategoryStockMaterial extends Model
{
    use HasFactory;
    protected $table = 'stock_category_stock_materials';
    protected $guarded = [];
}
