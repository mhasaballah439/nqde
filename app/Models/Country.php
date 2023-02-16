<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = 'countries';

    protected $guarded = [];
    public function scopeActive($q){
        return $q->where('status',1);
    }

    public function cities(){
        return $this->hasMany(City::class,'country_id');
    }
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
