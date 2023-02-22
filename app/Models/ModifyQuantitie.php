<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifyQuantitie extends Model
{
    use HasFactory;

    protected $table = 'modify_quantities';
    protected $guarded = [];

    public function stock_materials(){
        return $this->hasMany(ModifyQuantityStockMaterial::class,'modify_quantity_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function getReasonNameAttribute(){
        switch ($this->reason_type_id){
            case 1:
                return __('msg.cancel_return');
            case 2:
                return __('msg.modify_qty');
            case 3:
                return __('msg.fund_operations');
            case 4:
                return __('msg.expenses');
        }
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
