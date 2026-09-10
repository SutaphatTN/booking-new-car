<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use App\Models\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

/**
 * เงินเคลม (Form B) ของสถานที่ — 1 สถานที่ต่อ 1 ใบ (unique place_id)
 *
 * แถวจะถูกสร้างตอนกรอกครั้งแรกเท่านั้น สถานที่ที่ยังไม่เคยกรอกจะไม่มีแถวในตารางนี้
 * (หน้ารายการยิงจาก tb_source_place แล้ว left join มาที่นี่)
 *
 * ประวัติการกรอก/แก้ เก็บผ่าน LogsActivity ลง activity_logs (subject_type = 'SourcePlaceClaim')
 */
class SourcePlaceClaim extends Model
{
    use BrandScope;
    use LogsActivity;

    protected $table = 'tb_source_place_claim';

    protected $fillable = [
        'place_id',
        'form_b',
        'form_b_half',
        'status',
        'actual_amount',
        'received_date',
        'check_status',
        'note',
        'brand',
        'userZone',
        'branch',
        'UserInsert',
        'UserUpdate',
    ];

    protected $casts = [
        'form_b'        => 'decimal:2',
        'form_b_half'   => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function place()
    {
        return $this->belongsTo(SourcePlace::class, 'place_id');
    }

    /** คนที่กรอกครั้งแรก — UserInsert เขียนครั้งเดียวตอนสร้าง แก้ทีหลังไม่ทับ */
    public function creator()
    {
        return $this->belongsTo(User::class, 'UserInsert')->withTrashed();
    }

    /** คนที่แก้ล่าสุด */
    public function updater()
    {
        return $this->belongsTo(User::class, 'UserUpdate')->withTrashed();
    }

    /** ยอดที่เคลมได้จริงจาก Form B (คิดจาก config source.claim_share) */
    public static function shareOf($formB): ?float
    {
        return $formB === null || $formB === '' ? null
            : round((float) $formB * (float) config('source.claim_share', 0.5), 2);
    }

    /** ส่วนต่าง = Form B ตามสัดส่วน − ยอดเงินจริงในบัญชี (null ถ้ายังกรอกไม่ครบสองฝั่ง) */
    public function diff(): ?float
    {
        if ($this->form_b_half === null || $this->actual_amount === null) {
            return null;
        }
        return (float) $this->form_b_half - (float) $this->actual_amount;
    }
}
