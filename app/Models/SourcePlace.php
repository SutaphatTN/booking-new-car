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
        'extra_cost',
        'pending_extra',
        'extra_request_id',
        'extra_reason',
        'target',
        'status',
        'request_id',
        'settled_at',
        'settled_by',
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
        'settled_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function source()
    {
        // withTrashed: แหล่งที่มาที่ถูกลบไปแล้ว สถานที่/รายงานเดิมยังต้องแสดงชื่อได้
        return $this->belongsTo(TbSalecarType::class, 'salecar_type_id')->withTrashed();
    }

    public function request()
    {
        return $this->belongsTo(SourcePlaceRequest::class, 'request_id');
    }

    /** ใบเคลียร์ทั้งหมดของสถานที่ (รองรับเคลียร์/จ่ายหลายครั้ง — ก้อนใหญ่ทยอยจ่าย) */
    public function clears()
    {
        return $this->hasMany(SourcePlaceClear::class, 'place_id')->orderBy('id');
    }

    /** งบประมาณรวมที่เคลียร์ได้ = ประมาณค่าใช้จ่าย + งบเพิ่มที่อนุมัติแล้ว */
    public function effectiveBudget(): ?float
    {
        if ($this->cost === null && $this->extra_cost === null) {
            return null;
        }
        return (float) ($this->cost ?? 0) + (float) ($this->extra_cost ?? 0);
    }

    /** ยอดที่เคลียร์ไปแล้วรวมทุกใบ */
    public function clearedTotal(): float
    {
        return (float) $this->clears->sum('total');
    }

    /** งบคงเหลือที่ยังเคลียร์ได้ (null = ไม่ได้ตั้งงบ) */
    public function remainingBudget(): ?float
    {
        $budget = $this->effectiveBudget();
        return $budget === null ? null : $budget - $this->clearedTotal();
    }

    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    /** "ปิดยอด" แล้วหรือยัง — บัญชีกดปิดเอง (ใช้ตัดสินว่าจะซ่อนจากรายการที่ต้องทำ) */
    public function isSettled(): bool
    {
        return $this->settled_at !== null;
    }

    /**
     * ปิดยอดได้หรือยัง — ต้องมีใบเคลียร์อย่างน้อย 1 ใบ และจ่ายครบทุกใบก่อน
     * (กันปิดทั้งที่ยังมีงวดค้างจ่าย)
     */
    public function canSettle(): bool
    {
        return $this->clears->isNotEmpty()
            && !$this->clears->contains(fn($c) => !$c->pay_approved);
    }
}
