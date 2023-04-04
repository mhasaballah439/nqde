<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCheckStock extends Model
{
    use HasFactory;
    protected $table = 'stock_check_stocks';

    protected $guarded = [];

    public function stock(){
        return $this->belongsTo(Stock::class,'stock_id');
    }
}
