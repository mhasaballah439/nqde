<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';

    protected $guarded = [];

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
