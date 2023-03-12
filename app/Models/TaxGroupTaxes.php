<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxGroupTaxes extends Model
{
    use HasFactory;

    protected $table = 'tax_group_taxes';

    protected $guarded = [];
}
