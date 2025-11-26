<?php

namespace App\Console;

use App\Models\Salecar;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // เรียกงานลบใบจองอัตโนมัติ
        $schedule->call(function () {

            Salecar::whereNotNull('BookingDate')
                ->whereDate('BookingDate', '<=', now()->subDays(5))
                ->where(function ($query) {
                    $query->whereDoesntHave('remainingPayment')       // ยังไม่มีข้อมูล remainingPayment
                        ->orWhereHas('remainingPayment', function ($sub) {
                            $sub->whereNull('po_number');           // มีแต่ po_number ยังไม่ถูกกรอก
                        });
                })
                ->delete();
        })->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
