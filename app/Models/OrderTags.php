<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTags extends Model
{
    use HasFactory;
    protected $table = 'order_tags';
    protected $guarded = [];
}
