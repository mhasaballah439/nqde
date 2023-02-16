<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeesRool extends Model
{
    use HasFactory;

    protected $table = 'employees_rools';

    protected $guarded = [];

    public function employees(){
        return $this->hasMany(VendorEmployee::class,'role_id');
    }
}
