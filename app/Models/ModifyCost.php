<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyCost extends Model
{
    use HasFactory;


    protected $table = 'modify_costs';
    protected $guarded = [];

    public function stock_materials(){
        return $this->hasMany(ModifyCostStockMaterial::class,'modify_cost_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function getStatusNameAttribute(){
        switch ($this->status_id){
            case 1:
                return __('msg.draft');
            case 2:
                return __('msg.closed');
            case 3:
                return __('msg.cancel');
        }
    }
}
