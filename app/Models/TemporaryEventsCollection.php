<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryEventsCollection extends Model
{
    use HasFactory;
    protected $table = 'temporary_events_collections';
    protected $guarded = [];

    public function item_event(){
        return $this->belongsTo(TemporaryEvent::class,'event_id');
    }
}
