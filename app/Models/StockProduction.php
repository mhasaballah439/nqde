<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProduction extends Model
{
    use HasFactory;

    protected $table = 'stock_productions';
    protected $guarded = [];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function production_material(){
        return $this->hasMany(ProductionStockMaterial::class,'production_id');
    }
    public function getStatusNameAttribute()
    {
        switch ($this->status_id) {
            case 1:
                __('msg.undefined');
            case 2:
                __('msg.draft');
            case 3:
                __('msg.closed');
            case 4:
                __('msg.canceled');
        }
    }

}
