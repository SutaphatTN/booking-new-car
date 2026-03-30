<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FinancesExtraCom extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'finances_extracom';

    protected $fillable = [
        'financeID',
        'model_id',
        'com',
        'userZone',
		'brand',
        'branch',
    ];

    protected $dates = ['deleted_at'];

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
