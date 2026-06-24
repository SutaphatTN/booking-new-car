<?php

namespace App\Exports\carOrder;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * ไฟล์ต้นฉบับสำหรับนำเข้า WS — มีเฉพาะหัวคอลัมน์เปล่า ๆ ให้ผู้ใช้กรอกเอง
 */
class WsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['vin_number', 'WS'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => 'solid',
                    'startColor' => ['rgb' => 'c6efce'],
                ],
            ],
        ];
    }
}
