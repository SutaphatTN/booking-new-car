<?php

namespace App\Exports;

use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromView;

class BookingExport implements FromView
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        // -------------------------------
        // 1) ดึง CarOrder
        // -------------------------------
        $carOrders = CarOrder::with([
            'model',
            'subModel',
            'salecars.customer.prefix',
            'salecars.saleUser',
            'salecars.carOrderHistories'
        ])
            ->whereIn('status', ['approved', 'finished'])
            ->whereNot('car_status', 'Delivered')
            ->get();

        // -------------------------------
        // 2) ดึง SaleCar ยังไม่ผูก
        // -------------------------------
        $unboundSalesAll = Salecar::with(['customer.prefix', 'saleUser'])
            ->whereNull('CarOrderID')
            ->whereNotIn('con_status', [5, 9])
            ->get();

        // -------------------------------
        // 3) Group ข้อมูลตาม spec
        // -------------------------------
        $groups = [];

        foreach ($carOrders as $order) {
            $key = implode('-', [
                $order->model_id,
                $order->subModel_id,
                $order->option,
                $order->year,
                $order->color
            ]);

            $groups[$key]['orders'][] = $order;
        }

        foreach ($unboundSalesAll as $sale) {
            $key = implode('-', [
                $sale->model_id,
                $sale->subModel_id,
                $sale->option,
                $sale->Year,
                $sale->Color
            ]);

            $groups[$key]['unbound'][] = $sale;
        }

        // -------------------------------
        // 4) เตรียมข้อมูล Excel
        // -------------------------------
        $data = collect();
        $no = 1;

        foreach ($groups as $key => $items) {

            $orders  = $items['orders'] ?? [];
            $unbound = $items['unbound'] ?? [];

            if (empty($orders)) {
                continue;
            }

            $first = $orders[0];

            // -------------------------------
            // A) รวม sale ทั้งหมดก่อน (unbound + bound)
            // -------------------------------
            $mappedSales = collect();

            // unbound sale
            foreach ($unbound as $sale) {
                $mappedSales->push([
                    'order_id' => null,
                    'sale'     => $sale
                ]);
            }

            // bound sale
            foreach ($orders as $order) {
                foreach ($order->salecars as $sale) {

                    if (!in_array($sale->con_status, [5, 9])) {
                        $mappedSales->push([
                            'order_id' => $order->id,
                            'sale'     => $sale
                        ]);
                    }
                }
            }

            // -------------------------------
            // B) จัด sale ตามลำดับ order_id (null มาก่อน)
            // -------------------------------
            $mappedSales = $mappedSales->sortBy('order_id')->values();

            // -------------------------------
            // C) จัดเรียงตามจำนวนรถจริง
            // -------------------------------
            $bookingList = collect();

            foreach ($orders as $order) {

                // หาว่า sale ไหนตรงกับ order นี้
                $saleRow = $mappedSales->first(function ($s) use ($order) {
                    return $s['order_id'] === $order->id;
                });

                $bookingList->push([
                    'order' => $order,
                    'sale'  => $saleRow['sale'] ?? null
                ]);

                // mark ว่าใช้แล้ว
                if ($saleRow) {
                    $mappedSales = $mappedSales
                        ->reject(function ($s) use ($saleRow) {
                            return $s === $saleRow;
                        })
                        ->values();
                }
            }

            // -------------------------------
            // 4.3 เพิ่มข้อมูลลง Excel
            // -------------------------------
            $isFirstRow = true;

            foreach ($bookingList as $row) {

                $order = $row['order'];
                $sale  = $row['sale'];

                $data->push([
                    'No'       => $isFirstRow ? $no : '',
                    'model'    => $isFirstRow ? ($first->model->Name_TH ?? '-') : '',
                    'subModel' => $isFirstRow ? ($first->subModel->name ?? '-') : '',
                    'vin_number' => $order?->vin_number ?? '-',
                    'j_number'   => $order?->j_number ?? '-',
                    'option'   => $isFirstRow ? ($first->option ?? '-') : '',
                    'year'     => $isFirstRow ? ($first->year ?? '-') : '',
                    'color'    => $isFirstRow ? ($first->color ?? '-') : '',
                    'count'    => $isFirstRow ? count($orders) : '',

                    'customer' => $sale
                        ? ($sale->customer->prefix->Name_TH . ' ' . $sale->customer->FirstName . ' ' . $sale->customer->LastName)
                        : '-',

                    'sale'        => $sale?->saleUser?->name ?? '-',
                    'bookingDate' => $sale?->BookingDate ?? '-',
                    'status'      => $sale?->conStatus?->name ?? '-',

                    'statusCar' => $sale
                        ? ($sale->CarOrderID ? 'ผูกรถแล้ว' : 'ยังไม่ได้ผูกรถ')
                        : 'รถว่าง',

                    'daysBind' => ($sale && $sale->carOrderHistories?->changed_at)
                        ? Carbon::parse($sale->carOrderHistories->changed_at)->startOfDay()->diffInDays(now()->startOfDay()) . ' วัน'
                        : '-',
                ]);

                $isFirstRow = false;
            }

            $no++;
        }

        return view('purchase-order.report.booking', [
            'saleCar' => $data
        ]);
    }
}
