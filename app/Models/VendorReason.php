<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorReason extends Model
{
    use HasFactory;
    protected $table = 'vendor_reasons';

    protected $guarded = [];

    public function getReasonTypeAttribute(){
        switch ($this->reason_type_id){
            case 1:
                return __('msg.cancel_return');
            case 2:
                return __('msg.modify_qty');
            case 3:
                return __('msg.fund_operations');
            case 4:
                return __('msg.expenses');
        }
    }

    public function name($lang){
        if ($lang == 'ar')
            return $this->name_ar;
        else
            return $this->name_en;
    }
}
