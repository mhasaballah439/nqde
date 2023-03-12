<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeferredHistory extends Model
{
    use HasFactory;

    protected $table = 'user_deferred_histories';
    protected $guarded = [];

    public function getTypeNameAttribute(){
        switch ($this->type){
            case 1:
                return __('msg.payment_for_account');
            case 2:
                return __('msg.payment_due');
        }
    }
}
