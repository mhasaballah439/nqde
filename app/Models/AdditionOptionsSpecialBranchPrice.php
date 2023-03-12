<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionOptionsSpecialBranchPrice extends Model
{
    use HasFactory;
    protected $table = 'addition_options_special_branch_prices';
    protected $guarded = [];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
