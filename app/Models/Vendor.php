<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Vendor extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'vendors';
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function vendor_bouquets(){
        return $this->hasMany(VendorPlane::class,'vendor_id');
    }
    public function vendor_free_bouquets(){
        return $this->hasMany(VendorFreeBouquets::class,'vendor_id');
    }

    public function getIsActivePlanAttribute(){

        if(isset($this->vendor_bouquets) && count($this->vendor_bouquets) > 0){
            $plan = $this->vendor_bouquets()->whereDate('end_date','>',date('Y-m-d'))
                ->where('is_payment',1)->orderBy('id','DESC')->first();
            if (!$plan)
                $plan = $this->vendor_bouquets()->whereDate('end_date','>',date('Y-m-d'))
                    ->where('is_free_trail',1)->orderBy('id','DESC')->first();
        } elseif(isset($this->vendor_free_bouquets) && count($this->vendor_free_bouquets) > 0) {
            $plan = $this->vendor_free_bouquets()->whereDate('st_date', '<', date('Y-m-d'))
                ->whereDate('end_date', '>', date('Y-m-d'))
                ->orderBy('id', 'DESC')->first();
        }

        if (isset($plan))
            return 1;
        else
            return 0;
    }

    public function getActivePlanAttribute(){

        if(isset($this->vendor_bouquets) && count($this->vendor_bouquets) > 0){
            $plan = $this->vendor_bouquets()->whereDate('end_date','>',date('Y-m-d'))
                ->where('is_payment',1)->orderBy('id','DESC')->first();
            if (!$plan)
                $plan = $this->vendor_bouquets()->whereDate('end_date','>',date('Y-m-d'))
                    ->where('is_free_trail',1)->orderBy('id','DESC')->first();
        } elseif(isset($this->vendor_free_bouquets) && count($this->vendor_free_bouquets) > 0) {
            $plan = $this->vendor_free_bouquets()->whereDate('st_date', '<', date('Y-m-d'))
                ->whereDate('end_date', '>', date('Y-m-d'))
                ->orderBy('id', 'DESC')->first();
        }

        return isset($plan->plan) ? $plan->plan : [];
    }
    public function municipal_file()
    {
        return $this->morphOne(VendorMedia::class, 'mediable');
    }
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
}
