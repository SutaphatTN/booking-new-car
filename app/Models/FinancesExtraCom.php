<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FinancesExtraCom extends Model
{
    protected $table = 'finances_extracom';

    protected $fillable = [
        'financeID',
        'model_id',
        'com'
    ];

    public function financeAll()
    {
        return $this->belongsTo(Finance::class, 'financeID', 'id');
    }

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
    }

    public function getFormatUpdatedAttribute()
    {
        return $this->updated_at ? Carbon::parse($this->updated_at)->format('d-m-Y') : null;
    }
}
