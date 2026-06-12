<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbCampaignClaimStatus extends Model
{
    protected $table = 'tb_campaign_claim_status';

    protected $fillable = [
        'name',
    ];

    public function claims()
    {
        return $this->hasMany(CampaignClaim::class, 'status_id', 'id');
    }
}
