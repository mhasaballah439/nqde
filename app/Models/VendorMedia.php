<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorMedia extends Model
{
    use HasFactory;

    public function imageable()
    {
        return $this->morphTo();
    }
}
