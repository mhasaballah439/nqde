<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyCostStockMaterial extends Model
{
    use HasFactory;

    protected $table = 'modify_cost_stock_materials';

    protected $guarded = [];

    public function stock(){
        return $this->belongsTo(Stock::class,'stock_material_id');
    }
}
