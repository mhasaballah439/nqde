<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreTransfareStock extends Model
{
    use HasFactory;
    protected $table = 'store_transfare_stocks';
    protected $guarded = [];

    public function stock(){
        return $this->belongsTo(Stock::class,'stock_id');
    }
}
