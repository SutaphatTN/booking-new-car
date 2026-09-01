<?php

namespace App\Exports\saleCar;

use App\Services\SaleBookingQuery;
use App\Support\BrandFeature;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * รายงาน "ข้อมูลการจอง" — 2 sheet
 *   1) สรุปข้อมูลการจอง  : รายใบจอง 1 บรรทัด
 *   2) สรุป Lead Channel : ตารางไขว้แหล่งที่มาย่อย/ฝ่ายขาย × แหล่งที่มาหลัก
 *
 * query + map ข้อมูลไว้ที่นี่ที่เดียวแล้วส่งต่อให้ทั้งสอง sheet — sheet สรุปนับจากชุดเดียวกับ
 * ที่พิมพ์ในตารางเสมอ ตัวเลขเลยไม่มีทางเพี้ยนกัน และไม่ยิง query ซ้ำ (DB อยู่ remote)
 */
class SaleCarBookingExport implements WithMultipleSheets
{
    protected $fromDate;
    protected $toDate;
    protected $status;

    /**
     * maatwebsite/excel เรียก sheets() 2 รอบต่อการ export 1 ครั้ง
     * (Writer::export() และ WriterFactory::includesCharts()) → ถ้าไม่จำผลไว้ จะโหลดข้อมูลซ้ำฟรี ๆ
     */
    protected ?array $sheets = null;

    public function __construct($fromDate = null, $toDate = null, $status = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        $this->status   = $status;
    }

    public function sheets(): array
    {
        if ($this->sheets !== null) {
            return $this->sheets;
        }

        $rows = $this->rows();

        return $this->sheets = [
            new SaleCarBookingSheet($rows),
            new SaleCarLeadChannelSheet($rows, $this->fromDate, $this->toDate),
        ];
    }

    /** ใบจองที่เข้าเงื่อนไข map เป็น array พร้อมใช้ (คีย์ตรงกับที่ blade summary ใช้) */
    protected function rows(): Collection
    {
        $rows = SaleBookingQuery::base()
            // เซลล์ที่ลาออกแล้วถูก soft-delete ใน users → belongsTo ปกติจะคืน null แล้วรายงานขึ้น "-"
            // ทั้งที่ใบจองนั้นมีเจ้าของจริง จึงต้อง withTrashed (และ eager load ไปเลย กัน N+1)
            ->with(['saleUser' => fn($q) => $q->withTrashed()])
            ->when($this->fromDate && $this->toDate, function ($q) {
                $start = Carbon::createFromFormat('Y-m-d', $this->fromDate)->startOfDay();
                $end   = Carbon::createFromFormat('Y-m-d', $this->toDate)->endOfDay();
                $q->whereBetween('BookingDate', [$start, $end]);
            })
            ->when($this->status, function ($q) {
                $q->where('con_status', $this->status);
            })
            ->get();

        return $rows->map(function ($r) {
            $customerName = trim(
                ($r->customer->prefix->Name_TH ?? '') . ' ' .
                    ($r->customer->FirstName ?? '') . ' ' .
                    ($r->customer->LastName ?? '')
            );

            $model = $r->model->Name_TH ?? '-';
            $sub = $r->subModel->name ?? '-';
            $detailModel = $r->subModel->detail ?? null;

            $subModel = $detailModel
                ? "{$detailModel} - {$sub}"
                : $sub;

            $color = in_array($r->brand, [2, 3, 4])
                ? ($r->gwmColor->name ?? '-')
                : ($r->Color ?? '-');

            $interiorColor = BrandFeature::hasInteriorColor($r->brand)
                ? ($r->interiorColor->name ?? '-')
                : null;

            // ชื่อไฟแนนซ์: non-finance = ซื้อสด, finance = ชื่อบริษัทไฟแนนซ์ (ถ้าไม่ได้เลือก = -)
            $financeName = $r->payment_mode === 'finance'
                ? ($r->remainingPayment?->financeInfo?->FinanceCompany ?? '-')
                : 'ซื้อสด';

            // แหล่งที่มา: salecars.type ชี้ไปแหล่งที่มา "ย่อย" กลุ่มหลักอ่านจาก main_source ของมันอีกที
            // (getRelation เพราะ 'type' เป็นชื่อคอลัมน์ด้วย เรียก $r->type ตรง ๆ จะได้ค่า id)
            $sourceType    = $r->getRelation('type');
            $sourceMainKey = $sourceType?->main_source;

            return [
                'id'         => $r->id,
                'con_status' => $r->con_status,
                'customer'   => $customerName,
                'id_card'    => $r->customer?->IDNumber ?? '-',
                'phone'      => $r->customer?->formatted_mobile ?? '-',
                'address'    => $r->customer?->documentAddress?->short_address ?? '-',
                'sale' => $r->saleUser?->name ?? '-',
                'team' => $r->saleTeam?->name ?? '-',
                'model' => $model,
                'subModel' => $subModel,
                'option'     => $r->option ?? '-',
                'color'      => $color,
                'interior_color' => $interiorColor,
                'year'       => $r->Year ?? '-',
                'car_MSRP' => $r->carOrder?->car_MSRP ?? '-',
                'reservation_cost' => $r->CashDeposit ?? '-',
                'bookingDate' => $r?->format_booking_date ?? '',
                'name_fi'       => $financeName,
                'order_status' => $r->carOrder->orderStatus->name ?? '-',
                'contract_date' => $r?->remainingPayment->format_contract_date ?? '-',
                'ck_date' => $r?->format_ck_date ?? '-',
                'dms_date' => $r?->format_dms_date ?? '-',
                'DeliveryEstimateDate' => $r?->format_delivery_estimate_date ?? '-',
                'DeliveryDate' => $r?->format_delivery_date ?? '-',
                'status' => $r?->conStatus?->name ?? '-',
                'source_main_key' => $sourceMainKey,
                'source_main' => $sourceMainKey ? config("source.main.$sourceMainKey", $sourceMainKey) : '-',
                'source_sub' => $sourceType?->name ?? '-',
            ];
        });
    }
}
