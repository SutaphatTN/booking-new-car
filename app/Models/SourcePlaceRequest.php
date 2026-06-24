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
        'token',
        'period',
        'reject_reason',
        'decided_at',
        'brand',
        'userZone',
        'branch',
    ];

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

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
