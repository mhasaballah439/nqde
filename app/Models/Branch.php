<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'branches';

    protected $guarded = [];
    public function tags(){
        return $this->belongsToMany(Tag::class,'branch_tags','branch_id','tag_id');
    }

    public function delivery_areas(){
        return $this->belongsToMany(DeliveryArea::class,'branch_delivery_areas','branch_id','delivery_area_id');
    }

    public function employees(){
        return $this->belongsToMany(VendorEmployee::class,'employee_branches','branch_id','employee_id');
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
