<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SourcePlaceRequest extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'source_place_requests';

    protected $fillable = [
        'requester_id',
        'approver_id',
        'status',
        'type',
        'token',
        'period',
        'reject_reason',
        'decided_at',
        'brand',
        'userZone',
        'branch',
    ];

    public const TYPE_PLACE = 'place';
    public const TYPE_TOPUP = 'topup';

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function places()
    {
        return $this->hasMany(SourcePlace::class, 'request_id');
    }

    /** สถานที่ที่ผูกกับคำขอ "ของบเพิ่ม" (topup) ผ่าน extra_request_id */
    public function topupPlaces()
    {
        return $this->hasMany(SourcePlace::class, 'extra_request_id');
    }

    public function getIsTopupAttribute(): bool
    {
        return ($this->type ?? self::TYPE_PLACE) === self::TYPE_TOPUP;
    }

    /** รายการที่จะแสดงในเอกสาร/อีเมล: topup ใช้ topupPlaces, ปกติใช้ places */
    public function lines()
    {
        return $this->is_topup ? $this->topupPlaces : $this->places;
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
