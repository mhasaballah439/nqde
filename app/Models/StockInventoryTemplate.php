<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInventoryTemplate extends Model
{
    use HasFactory;

    protected $table = 'stock_inventory_templates';
    protected $guarded = [];
}
