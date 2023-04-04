<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreTransfare extends Model
{
    use HasFactory;
    protected $table = 'store_transfares';
    protected $guarded = [];

    public function from_branch(){
        return $this->belongsTo(Branch::class,'from_branch_id');
    }

    public function to_branch(){
        return $this->belongsTo(Branch::class,'to_branch_id');
    }

    public function from_store_house(){
        return $this->belongsTo(StoreHouse::class,'from_store_house_id');
    }

    public function to_store_house(){
        return $this->belongsTo(StoreHouse::class,'to_store_house_id');
    }

    public function stocks(){
        return $this->hasMany(StoreTransfareStock::class,'store_transfare_id');
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
