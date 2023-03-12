<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionOption extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'addition_options';
    protected $guarded = [];

    public function addition(){
        return $this->belongsTo(Addition::class,'addition_id');
    }

    public function tax_group(){
        return $this->belongsTo(TaxGroup::class,'tax_group_id');
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }

    public function getCostCalcMethodAttribute(){
        switch ($this->cost_calculation_method){
            case 1:
                return __('msg.fixed_price');
                case 2:
                return __('msg.of_stocks');
        }
    }

    public function stocks(){
        return $this->hasMany(AdditionOptionsStocks::class,'additional_option_id');
    }

    public function active_branches_special_price(){
        return $this->hasMany(AdditionOptionsSpecialBranchPrice::class,'additional_option_id')->where('active',1);
    }

    public function dactive_branches_special_price(){
        return $this->hasMany(AdditionOptionsSpecialBranchPrice::class,'additional_option_id')->where('active',0);
    }
}
