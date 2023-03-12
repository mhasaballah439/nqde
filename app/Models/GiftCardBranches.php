<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardBranches extends Model
{
    use HasFactory;
    protected $table = 'gift_card_branches';

    protected $guarded = [];

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
