<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permitions extends Model
{
    use HasFactory;

    protected $table = 'permitions';
    protected $guarded = [];

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
      public function desc($lang){
        if ($lang == 'ar')
            return $this->desc_ar;
        else
            return $this->desc_en;
    }

    public function children(){
        return $this->hasMany(Permitions::class,'parent_id');
    }
}
