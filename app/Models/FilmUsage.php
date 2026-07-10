<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilmUsage extends Model
{
    use SoftDeletes, BrandScope;

    protected $table = 'tb_film_usage';

    // การเบิกใช้ film แชร์กับ stock กองเดียวกันตามกลุ่ม brand (ดู config/brand.php)
    public $sharedByBrandGroup = true;

    protected $fillable = [
        'type',
        'order_date',
        'vin',
        'car_order_id',
        'salecar_id',
        'customer_name',
        'sale_person',
        'model_id',
        'car_brand',
        'car_model',
        'car_year',
        'customer_source',
        'insurance_company',
        'film_brand_id',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(FilmUsageItem::class, 'film_usage_id');
    }

    /**
     * ป้ายกำกับ "รุ่นรถ" สำหรับแสดงในตาราง/รายงาน
     *  - BP: ยี่ห้อ + รุ่น + ปี (กรอกเอง)
     *  - ทั่วไป: ชื่อรุ่นจากระบบ (อ่านข้าม brand ได้ — ดู model())
     *  - ไม่มีข้อมูลรุ่นจริงๆ → '-' (แบรนด์ดูจาก brandName() / คอลัมน์แบรนด์แยก)
     */
    public function carLabel(): string
    {
        $txt = $this->type === 'bp'
            ? trim(implode(' ', array_filter([$this->car_brand, $this->car_model, $this->car_year])))
            : ($this->model?->Name_TH ?? '');

        return $txt !== '' ? $txt : '-';
    }

    /** ชื่อแบรนด์เจ้าของงาน (stock ฟิล์มใช้ร่วมกันข้าม brand จึงต้องบอกให้ชัด) */
    public function brandName(): string
    {
        return config("brand.names.{$this->brand}") ?? '-';
    }

    /**
     * ปลดเฉพาะ BrandScope — ฟิล์มใช้ stock ร่วมกันข้าม brand (sharedByBrandGroup)
     * จึงต้องอ่านชื่อรุ่นของ brand อื่นได้ ไม่งั้นรุ่นรถจะว่าง
     * (ไม่ใช้ withoutGlobalScopes() เพราะยังต้องการ SoftDeletes ของ TbCarmodel)
     */
    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id')->withoutGlobalScope('brandAccess');
    }

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }
}
