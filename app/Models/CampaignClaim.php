<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignClaim extends Model
{
    use SoftDeletes;

    protected $table = 'campaign_claims';

    protected $casts = [
        'salecampaign_id' => 'int',
        'claim_amount' => 'float',
        'received_date' => 'date',
        'status_id' => 'int',
    ];

    protected $fillable = [
        'salecampaign_id',
        'claim_amount',
        'received_date',
        'status_id',
        'note',
        'userZone',
        'brand',
        'branch',
    ];

    protected $dates = ['deleted_at'];

    public function saleCampaign()
    {
        return $this->belongsTo(Salecampaign::class, 'salecampaign_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(TbCampaignClaimStatus::class, 'status_id', 'id');
    }

    public function getReceivedDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFormatReceivedDateAttribute()
    {
        return $this->received_date ? Carbon::parse($this->received_date)->format('d-m-Y') : null;
    }
}
