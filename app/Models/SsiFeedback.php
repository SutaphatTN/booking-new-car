<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsiFeedback extends Model
{
    protected $table = 'ssi_feedbacks';

    protected $fillable = [
        'ssi_record_id',
        'compliment',
        'suggestion',
        'complaint',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
