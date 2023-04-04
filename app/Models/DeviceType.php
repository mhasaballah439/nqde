<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    use HasFactory;
    protected $table = 'device_types';
    protected $guarded = [];

    public function scopeActive($q){
        return $q->where('active',1);
    }
}
