<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignApproval extends Model
{
    use SoftDeletes;

    protected $table = 'campaign_approvals';

    protected $fillable = [
        'campaign_id',
        'period_ym',
        'start_date',
        'end_date',
        'status',        // pending | approved | rejected
        'approval_token',
        'note',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'brand',
        'branch',
        'userZone',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
