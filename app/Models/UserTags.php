<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTags extends Model
{
    use HasFactory;

    protected $table = 'user_tags';
    protected $guarded = [];
}
