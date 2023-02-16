<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTag extends Model
{
    use HasFactory;

    protected $table = 'branch_tags';
    protected $guarded = [];
}
