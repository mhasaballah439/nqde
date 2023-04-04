<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCampaign extends Model
{
    use HasFactory;
    protected $table = 'vendor_campaigns';
    protected $guarded = [];

    public function users_file()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }
//1 -> email
//2 -> sms
//3 ->social_media
}
