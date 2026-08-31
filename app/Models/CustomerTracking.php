<?php

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use App\Models\Traits\SaleTeamScope;
use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TbInteriorColor;
use Carbon\Carbon;

class CustomerTracking extends Model
{
    use SoftDeletes, UserAccessScope, SaleTeamScope, TracksUserActions;

    protected $table = 'customer_trackings';

    protected $casts = [
        'booked_at'    => 'datetime',
        'cancelled_at' => 'datetime',
        // หลักฐานทดลองขับ — เก็บเป็น array ของ ['url' => share url, 'name' => ชื่อไฟล์เดิม] เหมือน salecars.attachment_url
        'test_drive_attachments' => 'array',
    ];

    protected $fillable = [
        'sale_id',
        'customer_id',
        'source_id',
        'place_id',
        'clip_add',
        'model_id',
        'sub_model_id',
        'year',
        'pricelist_color',
        'option',
        'color_id',
        'interior_color_id',
        'color_text',
        'userZone',
        'brand',
        'branch',
        'sale_team_id',
        'UserInsert',
        'UserUpdate',
        'UserDelete',
        'BookedBy',
        'booked_at',
        'cancelled_at',
        'CancelledBy',
        'end_type',
        'cancel_reason',
        'cancel_reason_note',
        'delivery_timeline_scoring',
        'test_drive_scoring',
        'occupation_scoring',
        'revenue_scoring',
        'model_interest_scoring',
        'purchase_type_scoring',
        'engagement_scoring',
        'test_drive_date',
        'test_drive_note',
        'test_drive_attachments',
        'customer_date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    /**
     * snapshot ทีมขาย — เขียน sale_team_id จาก "ทีมของผู้ขาย ณ ตอนนั้น" ทุกครั้งที่ sale_id เปลี่ยน
     *
     * ต้องเป็น snapshot ไม่ใช่ join สดตอนทำรายงาน เพราะถ้าเซลล์ย้ายทีม ยอดย้อนหลังของทีมเดิม
     * จะไหลตามไปทีมใหม่ทั้งก้อน (BI ที่เทียบรายปีจะเพี้ยน)
     *
     * วางไว้ที่ model แทนการไล่แก้ทุก controller — ครอบทั้งตอนสร้าง ตอนย้ายผู้ขาย
     * และ write path ที่จะเพิ่มในอนาคตด้วย
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            if (!$model->isDirty('sale_id')) {
                return;
            }

            $model->sale_team_id = $model->sale_id
                ? User::withoutGlobalScopes()->whereKey($model->sale_id)->value('sale_team_id')
                : null;
        });
    }

    public function saleTeam()
    {
        return $this->belongsTo(SaleTeam::class, 'sale_team_id', 'id');
    }
    public function sale()
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function brandInfo()
    {
        return $this->belongsTo(TbBrand::class, 'brand', 'id');
    }

    public function userInsert()
    {
        return $this->belongsTo(User::class, 'UserInsert');
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class, 'UserUpdate');
    }

    public function userDelete()
    {
        return $this->belongsTo(User::class, 'UserDelete');
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'BookedBy');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'CancelledBy');
    }

    public function source()
    {
        // withTrashed: แหล่งที่มาที่ถูกลบไปแล้ว ประวัติการติดตามเดิมยังต้องแสดงชื่อได้
        return $this->belongsTo(TbSalecarType::class, 'source_id')->withTrashed();
    }

    // แอด (คลิปที่ยิงแอด) — clip_add เก็บเป็น id อ้างถึง tb_ad
    // แอดที่ถูก "เก็บ" (is_active=0) ยังต้องแสดงชื่อในประวัติเดิมได้ จึงไม่ scope is_active
    public function ad()
    {
        return $this->belongsTo(Ad::class, 'clip_add');
    }

    public function place()
    {
        return $this->belongsTo(SourcePlace::class, 'place_id');
    }

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id');
    }

    public function subModel()
    {
        return $this->belongsTo(TbSubcarmodel::class, 'sub_model_id');
    }

    public function details()
    {
        return $this->hasMany(CustomerTrackingDetail::class, 'tracking_id');
    }

    /**
     * lead จากเพจ = บันทึกใบแรกที่ "คนเพิ่ม" (UserInsert) เป็นคนดูแลเพจ (role adminPage)
     * ใบติดตามที่เซลล์หาลูกค้าเองจะไม่มีใบนี้
     *
     * withTrashed เพราะดูว่า "ใครเป็นคนกรอก" ของข้อมูลย้อนหลัง
     * ถ้าคนดูแลเพจคนเก่าถูกลบ ใบที่เขาเคยกรอกก็ยังต้องนับ
     */
    public function firstPageLeadDetail(): ?CustomerTrackingDetail
    {
        return $this->details()
            ->whereHas('insertedBy', fn($q) => $q->withTrashed()->where('role', 'adminPage'))
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();
    }

    /**
     * ยังรอเซลล์ตอบกลับอยู่หรือไม่ — ใช้กติกาเดียวกับ view_performance_sale
     *   รอ = มีบันทึกจากเพจ (adminPage) แล้ว แต่ยังไม่มีบันทึกจาก role sale/lead_sale ตามหลังใบนั้น
     *
     * ใบติดตามที่ไม่มีบันทึกจากเพจเลย ถือว่าไม่ต้องรอ (ไม่ใช่ lead ที่เพจจ่ายมา)
     * ตรงกับ view เป๊ะ ๆ ตรงที่เทียบด้วย created_at ไม่ใช่ contact_date (contact_date กรอกย้อนหลังได้)
     */
    public function awaitingSaleReply(): bool
    {
        $pageLead = $this->firstPageLeadDetail();

        if (!$pageLead) {
            return false;
        }

        // ข้อมูลเก่าบางแถว created_at เป็น NULL — เทียบ "> NULL" ใน SQL ได้ NULL จึงไม่มีวันเจอคู่
        // view_performance_sale ก็ให้ sale_followed_up = 0 ในเคสนี้ ยึดผลเดียวกันไว้
        if ($pageLead->created_at === null) {
            return true;
        }

        return !$this->details()
            ->where('created_at', '>', $pageLead->created_at)
            ->whereHas('insertedBy', fn($q) => $q->withTrashed()->whereIn('role', ['sale', 'lead_sale']))
            ->exists();
    }

    public function latestDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')->latestOfMany();
    }

    // entry ผู้จัดการที่ใกล้ที่สุดในอนาคต (> วันนี้)
    public function nextManagerDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'min'], function ($q) {
                $q->where('entry_type', 'manager')
                  ->where('contact_date', '>', now()->toDateString());
            });
    }

    public function latestManagerDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'max'], function ($q) {
                $q->where('entry_type', 'manager');
            });
    }

    // entry ล่าสุด (ทุกประเภท) ที่วันที่ <= วันนี้
    public function latestPastDetail()
    {
        return $this->hasOne(CustomerTrackingDetail::class, 'tracking_id')
            ->ofMany(['contact_date' => 'max'], function ($q) {
                $q->whereDate('contact_date', '<=', now()->toDateString());
            });
    }

    public function wuColor()
    {
        return $this->belongsTo(TbColor::class, 'color_id');
    }

    public function interiorColor()
    {
        return $this->belongsTo(TbInteriorColor::class, 'interior_color_id');
    }

    public function getFormatTestDriveDateAttribute()
	{
		return $this->test_drive_date ? Carbon::parse($this->test_drive_date)->format('d-m-Y') : null;
	}
}
