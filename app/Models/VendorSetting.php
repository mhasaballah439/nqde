<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorSetting extends Model
{
    use HasFactory;
    protected $table = 'vendor_settings';
    protected $guarded = [];

    public function files()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }
}
