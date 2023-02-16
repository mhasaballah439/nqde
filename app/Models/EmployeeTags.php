<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeTags extends Model
{
    use HasFactory;

    protected $table = 'employee_tags';

    protected $guarded = [];

}
