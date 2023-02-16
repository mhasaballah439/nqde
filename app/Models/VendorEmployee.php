<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class VendorEmployee extends Authenticatable implements JWTSubject
{
    use HasFactory,SoftDeletes, Notifiable;

    protected $table = 'vendor_employees';

    protected $guarded = [];
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function branches(){
        return $this->belongsToMany(Branch::class,'employee_branches','employee_id','branch_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'employee_tags','employee_id','tag_id');
    }

    public function rool(){
        return $this->belongsTo(EmployeesRool::class,'role_id');
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
}
