<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SsiRecord extends Model
{
    use SoftDeletes;
    use BrandScope;

    protected $table = 'ssi_records';

    protected $fillable = [
        'salecar_id',
        'userZone',
        'brand',
        'branch',
        'UserInsert',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function salecar()
    {
        return $this->belongsTo(Salecar::class, 'salecar_id', 'id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function contacts()
    {
        return $this->hasMany(SsiContact::class, 'ssi_record_id')->orderBy('contact_date');
    }

    public function assessment()
    {
        return $this->hasOne(SsiAssessment::class, 'ssi_record_id');
    }

    public function payment()
    {
        return $this->hasOne(SsiPayment::class, 'ssi_record_id');
    }

    public function feedback()
    {
        return $this->hasOne(SsiFeedback::class, 'ssi_record_id');
    }

    public function resolution()
    {
        return $this->hasOne(SsiResolution::class, 'ssi_record_id');
    }

    /** เกณฑ์คะแนน SSI ที่ถือว่า "ผ่าน" (เปอร์เซ็นต์) */
    public const SSI_PASS_PERCENT = 90;

    /**
     * รายการช่องคะแนนที่ใช้คิด SSI + คะแนนเต็ม (ขึ้นกับ brand / สถานที่ส่งมอบ)
     * @return array{0: array<string>, 1: int}
     */
    public function ssiScoreFields(): array
    {
        $s = $this->salecar;

        if ($s && $s->brand == 2) {
            return [['gwm_q1', 'gwm_q2', 'gwm_q3', 'gwm_q4', 'gwm_q5', 'gwm_q6', 'gwm_q7', 'gwm_q8'], 40];
        }

        $isOffsite = $s && $s->delivery_location === 'Offsite';
        $fields = [
            'dw_website', 'q15_car_knowledge',
            'q17_service_responsibility', 'q18_sales_conditions', 'o27_car_condition',
            'fu_followup', 'recommend_showroom',
        ];
        if (!$isOffsite) {
            array_splice($fields, 1, 0, ['q11_facilities']);
        }

        return [$fields, $isOffsite ? 35 : 40];
    }

    /**
     * ข้อมูลคะแนน SSI: คะแนน% + จำนวนข้อที่ตอบ/ทั้งหมด + กรอกครบหรือไม่
     * @return array{score: ?float, answered: int, total: int, complete: bool}
     */
    public function ssiScoreInfo(): array
    {
        $this->loadMissing('assessment', 'salecar');
        $ass = $this->assessment;
        $s   = $this->salecar;

        if (!$ass || !$s) {
            return ['score' => null, 'answered' => 0, 'total' => 0, 'complete' => false];
        }

        [$fields, $maxScore] = $this->ssiScoreFields();
        $total    = count($fields);
        $answered = collect($fields)->filter(fn($f) => !is_null($ass->{$f}) && $ass->{$f} > 0);
        $count    = $answered->count();

        $score = $count > 0
            ? min(round(($answered->sum(fn($f) => (int) $ass->{$f}) / $maxScore) * 100, 2), 100)
            : null;

        return [
            'score'    => $score,
            'answered' => $count,
            'total'    => $total,
            'complete' => $total > 0 && $count === $total,
        ];
    }

    /**
     * คะแนน SSI รวมเป็นเปอร์เซ็นต์ (อ้างอิงสูตรเดียวกับรายงาน Excel)
     * คืน null ถ้ายังไม่ได้ประเมิน (ไม่มีข้อที่ตอบ)
     */
    public function ssiScorePercent(): ?float
    {
        return $this->ssiScoreInfo()['score'];
    }

    /** มีการระบุวันที่แก้ไขปัญหาแล้วหรือไม่ */
    public function hasResolutionDate(): bool
    {
        $this->loadMissing('resolution');
        return $this->resolution?->resolution_date !== null;
    }

    /**
     * ปิดงาน ("ตรวจสอบเสร็จแล้ว") ได้เมื่อ
     * - คะแนน SSI >= 90% หรือ
     * - มีวันที่แก้ไขปัญหาแล้ว
     */
    public function canMarkComplete(): bool
    {
        $score = $this->ssiScorePercent();
        if ($score !== null && $score >= self::SSI_PASS_PERCENT) {
            return true;
        }
        return $this->hasResolutionDate();
    }

    /**
     * จำนวนเคสที่ "ยังไม่เรียบร้อย": ยังไม่ปิดงาน + กรอกคะแนนครบ + SSI < 90%
     * + ยังไม่ได้ระบุวันที่แก้ไขปัญหา (อยู่ภายใต้ brand ของผู้ใช้ตาม global scope)
     */
    public static function pendingLowScoreCount(): int
    {
        return self::with(['assessment', 'salecar', 'resolution'])
            ->whereNull('completed_at')
            ->whereHas('salecar', fn($q) => $q->where('con_status', 5)->whereNotNull('DeliveryDate'))
            ->get()
            ->filter(function ($rec) {
                if ($rec->hasResolutionDate()) {
                    return false;
                }
                $info = $rec->ssiScoreInfo();
                return $info['complete'] && $info['score'] !== null && $info['score'] < self::SSI_PASS_PERCENT;
            })
            ->count();
    }
}
