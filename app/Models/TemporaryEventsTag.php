<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryEventsTag extends Model
{
    use HasFactory;
    protected $table = 'temporary_events_tags';
    protected $guarded = [];
}
