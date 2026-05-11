<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsiContact extends Model
{
    use SoftDeletes;

    protected $table = 'ssi_contacts';

    protected $casts = [
        'contacted'         => 'boolean',
        'interview_success' => 'boolean',
        'contact_date'      => 'date',
    ];

    protected $fillable = [
        'ssi_record_id',
        'contact_date',
        'contacted',
        'interview_success',
        'remark',
    ];

    public function ssiRecord()
    {
        return $this->belongsTo(SsiRecord::class, 'ssi_record_id');
    }
}
