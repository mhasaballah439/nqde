<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorPreparationTrack extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'vendor_preparation_tracks';
    protected $guarded = [];
}
