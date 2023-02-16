<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';
    protected $guarded = [];

    public function getTypeNameAttribute()
    {
        switch ($this->type) {
            case 1:
                return __('msg.customers');
            case 2:
                return __('msg.branches');
            case 3:
                return __('msg.stoks');
            case 4:
                return __('msg.orders');
            case 5:
                return __('msg.suppliers');
            case 6:
                return __('msg.users');
            case 7:
                return __('msg.products');
            case 8:
                return __('msg.devices');
            case 9:
                return __('msg.storehouses');
        }
    }
}
