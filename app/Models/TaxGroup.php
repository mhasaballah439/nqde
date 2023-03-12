<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxGroup extends Model
{
    use HasFactory;

    protected $table = 'tax_groups';

    protected $guarded = [];
    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
    public function taxes(){
        return $this->belongsToMany(Tax::class,'tax_group_taxes','group_id' , 'tax_id');
    }
}
