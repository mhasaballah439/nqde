<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceTags extends Model
{
    use HasFactory;

    protected $table = 'device_tags';
    protected $guarded = [];
}
