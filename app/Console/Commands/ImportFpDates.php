<?php

namespace App\Console\Commands;

use App\Models\CarOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * นำเข้า "วันที่ FP" (fp_date) และ "วันที่ปิด FP" (fp_close_date) ครั้งเดียว จับคู่ด้วย vin_number
 *
 *  - อัปเดตเฉพาะคันที่ car_status = Available / Booked (ตัด Delivered ทิ้ง)
 *  - เติมเฉพาะช่องที่ "ยังว่าง" เท่านั้น (ไม่ทับค่าเดิม) -> รันซ้ำได้ปลอดภัย
 *  - คันที่ vin ตรง ถือว่าเป็น FP -> ตั้ง payment_type = 'fp_tisco'
 *  - วันที่ปิด FP ต้องไม่ก่อน Billing date (ไม่งั้นข้าม + แจ้งเตือน)
 *
 * ไฟล์ต้นฉบับ (.xlsx / .csv) หัวคอลัมน์: vin_number, fp_date, [fp_close_date] (คอลัมน์ปิด FP มีหรือไม่มีก็ได้)
 * วันที่รองรับ: Excel date จริง (แนะนำ), yyyy-mm-dd, dd/mm/yyyy, dd-mm-yyyy (รับปี พ.ศ. อัตโนมัติ)
 *
 * ใช้:  php artisan fp:import-dates "app/fp.xlsx"
 *      php artisan fp:import-dates "app/fp.xlsx" --dry-run   (ดูผลก่อน ไม่บันทึก)
 */
class ImportFpDates extends Command
{
    protected $signature = 'fp:import-dates {file : path ไฟล์ .xlsx/.csv} {--dry-run : ทดลองรัน ไม่บันทึกจริง} {--close-mdy : คอลัมน์วันปิด FP ที่เป็น Excel-date เรียงแบบ เดือน/วัน/ปี ให้สลับเป็น วัน/เดือน}';

    protected $description = 'นำเข้าวันที่ FP (fp_date) + วันที่ปิด FP (fp_close_date) เข้าตาราง car_order จับคู่ด้วย vin_number';

    private const ALLOWED_STATUS = ['Available', 'Booked'];

    public function handle(): int
    {
        $path = $this->argument('file');
        $dry  = (bool) $this->option('dry-run');

        if (!is_file($path)) {
            $this->error("ไม่พบไฟล์: {$path}");
            return self::FAILURE;
        }

        try {
            $rows = Excel::toCollection(new class implements WithHeadingRow {}, $path)->first();
        } catch (\Throwable $e) {
            $this->error('อ่านไฟล์ไม่สำเร็จ: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (!$rows || $rows->isEmpty()) {
            $this->error('ไม่มีข้อมูลในไฟล์ (ตรวจหัวคอลัมน์ vin_number, fp_date)');
            return self::FAILURE;
        }

        $updatedDate = $updatedClose = 0;
        $notFound = $skipStatus = $skipDateHas = $skipCloseHas = $skipCloseInvalid = 0;
        $badDate = $badClose = $blank = 0;
        $hasCloseColumn = false;
        $issues = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // +2 = ข้ามหัวตาราง + index เริ่ม 0
            $vin  = trim((string) ($row['vin_number'] ?? ''));

            $rawDate  = $row['fp_date'] ?? null;
            $rawClose = $row['fp_close_date'] ?? $row['close_date'] ?? $row['fp_close'] ?? null;

            if (array_key_exists('fp_close_date', $row->toArray())
                || array_key_exists('close_date', $row->toArray())
                || array_key_exists('fp_close', $row->toArray())) {
                $hasCloseColumn = true;
            }

            if ($vin === '') {
                $blank++;
                continue;
            }

            $date  = $this->parseDate($rawDate);
            $close = $this->parseDate($rawClose, (bool) $this->option('close-mdy'));

            // มีค่าในเซลล์แต่แปลงเป็นวันที่ไม่ได้ -> แจ้งเตือน
            if ($rawDate !== null && trim((string) $rawDate) !== '' && !$date) {
                $badDate++;
                $issues[] = "แถว {$line}: vin {$vin} — fp_date อ่านไม่ได้ ('" . trim((string) $rawDate) . "')";
            }
            if ($rawClose !== null && trim((string) $rawClose) !== '' && !$close) {
                $badClose++;
                $issues[] = "แถว {$line}: vin {$vin} — วันที่ปิด FP อ่านไม่ได้ ('" . trim((string) $rawClose) . "')";
            }

            if (!$date && !$close) {
                continue;
            }

            $matches = CarOrder::where('vin_number', $vin)->get();
            if ($matches->isEmpty()) {
                $notFound++;
                $issues[] = "แถว {$line}: vin {$vin} — ไม่พบใน car_order";
                continue;
            }

            $eligible = $matches->filter(fn ($o) => in_array($o->car_status, self::ALLOWED_STATUS, true));
            if ($eligible->isEmpty()) {
                $skipStatus++;
                continue;
            }

            foreach ($eligible as $o) {
                // ── fp_date : เติมเฉพาะที่ว่าง ──
                if ($date) {
                    if (empty($o->getRawOriginal('fp_date'))) {
                        $o->fp_date      = $date->format('Y-m-d');
                        $o->payment_type = 'fp_tisco';
                        $updatedDate++;
                    } else {
                        $skipDateHas++;
                    }
                }

                // ── fp_close_date : เติมเฉพาะที่ว่าง + ต้องไม่ก่อน billing ──
                if ($close) {
                    if (!empty($o->getRawOriginal('fp_close_date'))) {
                        $skipCloseHas++;
                    } else {
                        // billing = ค่าที่มีอยู่ หรือค่าที่เพิ่งเซ็ตในรอบนี้
                        $billing = $o->getRawOriginal('fp_date') ?: ($date ? $date->format('Y-m-d') : null);

                        if ($billing && $close->format('Y-m-d') >= substr((string) $billing, 0, 10)) {
                            $o->fp_close_date = $close->format('Y-m-d');
                            $o->payment_type  = 'fp_tisco';
                            $updatedClose++;
                        } else {
                            $skipCloseInvalid++;
                            $issues[] = "แถว {$line}: vin {$vin} — วันปิด FP ({$close->format('d/m/Y')}) ก่อน billing หรือไม่มี billing";
                        }
                    }
                }

                if (!$dry && $o->isDirty()) {
                    $o->save();
                }
            }
        }

        $this->newLine();
        $this->info(($dry ? '[DRY-RUN] ' : '') . 'สรุปผลนำเข้าวันที่ FP');
        $this->table(['รายการ', 'จำนวน'], [
            ['อ่านทั้งหมด (แถวมี vin)', $rows->count() - $blank],
            ['✅ อัปเดต fp_date (Available/Booked + ว่าง)', $updatedDate],
            ['✅ อัปเดต วันปิด FP (Available/Booked + ว่าง)', $updatedClose],
            ['⏭ ข้าม: มี fp_date เดิมอยู่แล้ว', $skipDateHas],
            ['⏭ ข้าม: มีวันปิด FP เดิมอยู่แล้ว', $skipCloseHas],
            ['⏭ ข้าม: สถานะไม่ใช่ Available/Booked', $skipStatus],
            ['⚠ ข้าม: ไม่พบ vin ใน car_order', $notFound],
            ['⚠ ข้าม: วันปิด FP ก่อน billing/ไม่มี billing', $skipCloseInvalid],
            ['⚠ ข้าม: fp_date อ่านไม่ได้', $badDate],
            ['⚠ ข้าม: วันปิด FP อ่านไม่ได้', $badClose],
        ]);

        if (!$hasCloseColumn) {
            $this->warn('ℹ ไม่พบคอลัมน์ "fp_close_date" ในไฟล์ — ยังไม่ได้นำเข้าวันปิด FP');
            $this->line('  เพิ่มคอลัมน์ชื่อ fp_close_date (คู่กับ vin_number) แล้วเซฟไฟล์ จากนั้นรันคำสั่งนี้ซ้ำได้เลย');
        }

        if ($issues) {
            $this->warn('รายการที่ต้องตรวจ (สูงสุด 30 แถว):');
            foreach (array_slice($issues, 0, 30) as $msg) {
                $this->line('  - ' . $msg);
            }
            if (count($issues) > 30) {
                $this->line('  ... และอีก ' . (count($issues) - 30) . ' รายการ');
            }
        }

        if ($dry) {
            $this->newLine();
            $this->comment('นี่คือ DRY-RUN — ยังไม่บันทึก ลบ --dry-run เพื่อบันทึกจริง');
        }

        return self::SUCCESS;
    }

    /**
     * สลับเดือน/วัน สำหรับค่าที่มาจาก Excel-date ซึ่งต้นทางเรียงแบบ MDY
     * (สลับได้เฉพาะเมื่อ "วัน" ที่เก็บไว้ <= 12 เท่านั้น ไม่งั้นคืนค่าเดิม)
     */
    private function maybeSwapMdy(Carbon $d, bool $swap): Carbon
    {
        if ($swap && (int) $d->day <= 12) {
            return Carbon::create($d->year, (int) $d->day, (int) $d->month)->startOfDay();
        }
        return $d;
    }

    /**
     * แปลงค่าจากเซลล์เป็นวันที่ — คืน null ถ้าอ่านไม่ได้ (จะไม่เดามั่ว)
     */
    private function parseDate($raw, bool $swapSerialMdy = false): ?Carbon
    {
        if ($raw === null) {
            return null;
        }

        // เซลล์เป็นวันที่จริง (Carbon/DateTime)
        if ($raw instanceof \DateTimeInterface) {
            return $this->maybeSwapMdy(Carbon::instance($raw), $swapSerialMdy);
        }

        // Excel serial date (ตัวเลข)
        if (is_numeric($raw)) {
            try {
                return $this->maybeSwapMdy(Carbon::instance(ExcelDate::excelToDateTimeObject((float) $raw)), $swapSerialMdy);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }

        // ลองรูปแบบชัดเจนตามลำดับ (กันสับสน d/m กับ m/d)
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'j/n/Y', 'j-n-Y', 'd/m/y', 'd-m-y'] as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $s);
                if ($d && $d->format($fmt) === $s) {
                    // แปลงปี พ.ศ. -> ค.ศ. (เช่น 2569 -> 2026)
                    if ($d->year >= 2400) {
                        $d->subYears(543);
                    }
                    return $d->startOfDay();
                }
            } catch (\Throwable $e) {
                // ลองรูปแบบถัดไป
            }
        }

        return null;
    }
}
