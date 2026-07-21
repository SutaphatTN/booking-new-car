<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** รายการแจกแจง "ประมาณค่าใช้จ่าย" ของสถานที่ (ตอนตั้งงบ) — คู่กับ SourcePlaceClearItem ตอนเคลียร์จริง */
class SourcePlaceBudgetItem extends Model
{
    protected $table = 'source_place_budget_items';

    protected $fillable = [
        'place_id',
        'type',
        'amount',
    ];

    public function place()
    {
        return $this->belongsTo(SourcePlace::class, 'place_id');
    }
}
