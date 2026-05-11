<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsiResolution extends Model
{
    protected $table = 'ssi_resolutions';

    protected $casts = [
        'resolution_date'            => 'date',
        'correction_form_sent_date'  => 'date',
    ];

    protected $fillable = [
        'ssi_record_id',
        'cro_comment',
        'sm_resolution',
        'resolution_date',
        'resolution_status',
        'correction_form_sent_date',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
