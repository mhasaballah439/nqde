<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function tags(){
        return $this->belongsToMany(Tag::class,'user_tags','user_id','tag_id');
    }

    public function orders(){
        return $this->hasMany(Order::class,'user_id');
    }

    public function getLatestOrderAttribute(){
        return Order::where('user_id',$this->id)->latest()->first();
    }

    public function deferred_history(){
        return $this->hasMany(UserDeferredHistory::class,'user_id');
    }

    public function address(){
        return $this->hasMany(UserAddress::class,'user_id');
    }
    public function getGenderNameAttribute(){
        switch ($this->gender){
            case 1:
                return __('msg.mail');
            case 2:
                return __('msg.femail');
        }
    }
}
