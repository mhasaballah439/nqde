<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersCard extends Model
{
    use HasFactory;

    protected $table = 'users_cards';

    protected $guarded = [];
}
