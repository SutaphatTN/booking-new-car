<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SourcePlace extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'tb_source_place';

    protected $fillable = [
        'salecar_type_id',
        'las_number',
        'start_date',
        'end_date',
        'location',
        'expense_type',
        'cost',
        'target',
        'status',
        'request_id',
        'brand',
        'userZone',
        'branch',
        'UserInsert',
    ];

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    protected $dates = ['deleted_at'];

    public function source()
    {
        return $this->belongsTo(TbSalecarType::class, 'salecar_type_id');
    }

    public function request()
    {
        return $this->belongsTo(SourcePlaceRequest::class, 'request_id');
    }

    public function clear()
    {
        return $this->hasOne(SourcePlaceClear::class, 'place_id');
    }
}
