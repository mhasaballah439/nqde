<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorNotifacationActive extends Model
{
    use HasFactory;
    protected $table = 'vendor_notifacation_actives';
    protected $guarded = [];

    public function branches(){
        return $this->hasMany(ActiveNotifyBranch::class,'notify_id');
    }
}
