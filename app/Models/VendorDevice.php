<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorDevice extends Model
{
    use HasFactory;
    protected $table = 'vendor_devices';
    protected $guarded = [];

    public function tags(){
        return $this->belongsToMany(Tag::class,'device_tags','device_id','tag_id');
    }

    public function type(){
        return $this->belongsTo(DeviceType::class,'type_id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function orders(){
        return $this->hasMany(Order::class,'device_id');
    }
    public function casher_device(){
        return $this->belongsTo(VendorCasherDevice::class,'device_id');
    }
    public function getStatusNameAttribute(){
        switch ($this->status_id){
            case 1:
                return __('msg.new');
            case 2:
                return __('msg.used');
        }
    }
}
