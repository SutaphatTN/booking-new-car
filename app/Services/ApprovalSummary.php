<?php

namespace App\Services;

use App\Models\FinancesConfirm;
use App\Models\Salecar;

/**
 * ยอดสรุปของ "ใบขออนุมัติเกินงบ" — สูตรชุดเดียวกับที่ส่งไปในอีเมลขออนุมัติ
 * (บล็อก "สรุปยอดแคมเปญ" / "รายการหัก" / "ยอดที่เหลือ" ใน emails/sale-request.blade.php)
 *
 * ย้ายออกมาจาก PurchaseOrderController::buildApprovalData() เพื่อให้รายงานเกินงบ
 * ใช้สูตรเดียวกันได้โดยไม่ต้องก็อปเลข — แก้สูตรที่เดียวแล้วทั้งเมลและรายงานตรงกันเสมอ
 * (controller ยังเรียกผ่านเมธอดเดิมอยู่ พฤติกรรมไม่เปลี่ยน)
 *
 * ⚠ build() ไม่ยิง query relation เอง — ผู้เรียกต้อง load RELATIONS มาให้ก่อน
 *   รายงานมีหลายสิบแถว ถ้า load รายแถวจะกลายเป็น N+1 (DB อยู่ remote ~50ms/query)
 */
class ApprovalSummary
{
    /** relation ที่ build() ต้องใช้ — controller/รายงาน ต้อง eager load ชุดนี้มาก่อน */
    public const RELATIONS = [
        'accessories',
        'campaigns.campaign.type',
        'campaigns.campaign.appellation',
        'remainingPayment.financeInfo',
        'model',
    ];

    /**
     * @param FinancesConfirm|null $fnCon ใบ FN ของใบขายนี้ — ส่งมาเองได้เพื่อเลี่ยง query รายแถว
     *                                    ไม่ส่ง = ไปหาเองใน DB (พฤติกรรมเดิมของ controller)
     */
    public static function build(Salecar $saleCar, ?FinancesConfirm $fnCon = null): array
    {
        // 1. ราคาขาย จาก price_sub
        $priceSub = (float) ($saleCar->price_sub ?? 0);

        $isFinance = $saleCar->payment_mode === 'finance';

        // 3. margin = ราคาขาย × 2% — brand 2 ไม่มี margin (ไม่คิดเข้ายอดรวมแคมเปญ)
        $hasMargin = (int) $saleCar->brand !== 2;
        $margin = $hasMargin ? $priceSub * 0.02 : 0.0;

        // 2. ri (cashSupport) จากแคมเปญที่ใช้ — นับเฉพาะ type = 1 (RI) และ type = 2 (On-Top)
        //    เช็คตาม type ของแคมเปญที่ใช้จริง (tb_campaign_type แยก brand อยู่แล้ว จึงไม่ต้อง hardcode ตาม brand)
        $usedCampaigns = $saleCar->campaigns->filter(
            fn($c) => in_array((int) ($c->campaign?->type?->type ?? 0), [1, 2], true)
        );
        $ri = $usedCampaigns->sum(fn($c) => (float) ($c->CashSupport ?? 0));
        $campaignDetails = $usedCampaigns->map(fn($c) => [
            'name'   => trim(($c->campaign?->appellation?->name ?? '') . ' (' . ($c->campaign?->type?->name ?? '') . ')'),
            'amount' => (float) ($c->CashSupport ?? 0),
        ])->values();

        // 4. com finance (port calculateComFin จากหน้า FN)
        $comFin = self::comFinance($saleCar, $fnCon);

        // 4.1 บวกหัว (90%) — มีเฉพาะเคสจัดไฟแนนซ์
        $markup90 = $isFinance ? (float) ($saleCar->Markup90 ?? 0) : 0.0;

        // 4.2 ลูกค้าจ่ายเพิ่ม — มีทั้งเงินสด/ไฟแนนซ์ (คนละคอลัมน์)
        $customerExtra = $isFinance
            ? (float) ($saleCar->other_cost_fi ?? 0)
            : (float) ($saleCar->other_cost ?? 0);

        // 5. ยอดรวมแคมเปญ = ri + margin + com finance + บวกหัว(90%) + ลูกค้าจ่ายเพิ่ม
        $campaignTotal = $ri + $margin + $comFin + $markup90 + $customerExtra;

        // 6. ของแถม = ราคาทุนอะไหล่ (cost_spare) ของของแถมทั้งหมด + รายละเอียด
        //    ใช้ค่าที่ snapshot ไว้ในใบขาย ไม่ใช่ค่าปัจจุบันใน master — แก้ราคา master แล้วใบเก่าต้องไม่ขยับ
        $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift');
        $giftTotal = $giftAccessories->sum(fn($a) => $a->usedCostSpare());
        $giftDetails = $giftAccessories->map(fn($a) => [
            'detail' => $a->detail,
            'note'   => $a->pivot->note, // ฟิล์ม: ความเข้ม/ตำแหน่งที่ติด
            'amount' => $a->usedCostSpare(),
        ])->values();

        // 7. ส่วนลด
        $discount = $isFinance
            ? (float) ($saleCar->discount ?? 0)
            : (float) ($saleCar->PaymentDiscount ?? 0);

        // 7.1 ส่วนลดเงินดาวน์ — มีเฉพาะเคสจัดไฟแนนซ์ (เงินสดไม่มี) หักเหมือนส่วนลด
        $downPaymentDiscount = $isFinance
            ? (float) ($saleCar->DownPaymentDiscount ?? 0)
            : 0.0;

        // 7.2 Vat ของแถม — เคสจัดไฟแนนซ์เท่านั้น (ตรงกับสูตรยอดคงเหลือแคมเปญในหน้าใบจอง)
        $accessoryGiftVat = $isFinance
            ? (float) ($saleCar->AccessoryGiftVat ?? 0)
            : 0.0;

        // 8. ยอดที่เหลือ = ยอดรวมแคมเปญ − ของแถม − ส่วนลด − ส่วนลดเงินดาวน์ − Vat ของแถม
        $remaining = $campaignTotal - $giftTotal - $discount - $downPaymentDiscount - $accessoryGiftVat;

        return [
            'price_sub'        => $priceSub,
            'margin'           => $margin,
            'has_margin'       => $hasMargin,
            'ri'               => $ri,
            'campaign_details' => $campaignDetails,
            'com_fin'          => $comFin,
            'markup90'         => $markup90,
            'customer_extra'   => $customerExtra,
            'campaign_total'   => $campaignTotal,
            'gift_total'       => $giftTotal,
            'gift_details'     => $giftDetails,
            'discount'         => $discount,
            'is_finance'       => $isFinance,
            'down_payment_discount' => $downPaymentDiscount,
            'accessory_gift_vat'    => $accessoryGiftVat,
            'remaining'        => $remaining,
        ];
    }

    /** com finance (port calculateComFin จากหน้า FN) */
    public static function comFinance(Salecar $saleCar, ?FinancesConfirm $fnCon = null): float
    {
        $rp = $saleCar->remainingPayment;
        $fnCon ??= FinancesConfirm::withoutGlobalScopes()->where('SaleID', $saleCar->id)->first();

        // ถ้าทำ FN แล้ว (มี com_fin บันทึกไว้) ใช้ค่านั้นเลย
        if ($fnCon && $fnCon->com_fin !== null && (float) $fnCon->com_fin != 0.0) {
            return (float) $fnCon->com_fin;
        }

        // excellent: ใช้ค่าใน FN ถ้ามี ไม่งั้น fallback เป็น balanceFinance (เหมือน editFN)
        $excellent = (float) ($fnCon->excellent ?? $saleCar->balanceFinance ?? 0);

        $alp      = (float) ($rp?->total_alp ?? 0);
        $interest = (float) ($rp?->interest ?? 0) / 100;
        $typeCom  = (float) ($rp?->type_com ?? 0) / 100;
        $period   = (float) ($rp?->period ?? 0);
        $maxYear  = (float) ($rp?->financeInfo?->max_year ?? 0);
        $tax      = (float) ($rp?->financeInfo?->tax ?? 0) / 100;

        $realYear = $period > 0 ? $period / 12 : 0;
        $useYear  = $maxYear > 0 ? min($realYear, $maxYear) : $realYear;

        $base = $excellent + $alp;
        $per  = $typeCom * $interest * $useYear;
        $com  = ($base * $per) / 1.07;

        return $com * 1.07 - $com * $tax;
    }

    /**
     * "สรุปการแถม" — ยอดเกินงบ/งบเหลือเต็มจำนวน
     * balanceCampaign เก็บค่าที่หาร 2 แล้ว (ติดลบ = เกินงบ) จึงคูณกลับ ×2
     * ตรงกับที่อีเมลขออนุมัติแสดงในบล็อก "สรุปยอด"
     */
    public static function giftBalanceFull(Salecar $saleCar): float
    {
        return (float) ($saleCar->balanceCampaign ?? 0) * 2;
    }

    /**
     * "เปอร์เซ็นต์หัก" — คิดย้อนจากยอดที่ผู้จัดการตกลงหักจริง หารด้วยยอดเกินงบเต็มจำนวน
     * ปกติได้ 10% แต่ถ้าผู้จัดการแก้ยอด เปอร์เซ็นต์จะขยับตาม
     * null = ยังไม่ได้กรอกยอดหัก หรือใบนี้ไม่ได้เกินงบ
     */
    public static function deductPercent(Salecar $saleCar, $deduct = null): ?float
    {
        $deduct ??= $saleCar->approval_commission_deduct;
        $balFull = self::giftBalanceFull($saleCar);

        if ($deduct === null || $balFull >= 0 || abs($balFull) <= 0) {
            return null;
        }

        return abs((float) $deduct) / abs($balFull) * 100;
    }
}
