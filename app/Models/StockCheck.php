<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCheck extends Model
{
    use HasFactory;

    protected $table = 'stock_checks';
    protected $guarded = [];
    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function stocks(){
        return $this->hasMany(StockCheckStock::class,'stock_check_id');
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
