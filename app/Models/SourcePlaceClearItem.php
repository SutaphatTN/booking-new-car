<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourcePlaceClearItem extends Model
{
    protected $table = 'source_place_clear_items';

    protected $fillable = [
        'clear_id',
        'type',
        'amount',
    ];

    public function clear()
    {
        return $this->belongsTo(SourcePlaceClear::class, 'clear_id');
    }
}
