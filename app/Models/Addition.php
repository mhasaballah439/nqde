<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addition extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'additions';
    protected $guarded = [];

    public function addition_options(){
        return $this->hasMany(AdditionOption::class,'addition_id');
    }

    public function products(){
        return $this->hasMany(ProductAddition::class,'addition_id');
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
