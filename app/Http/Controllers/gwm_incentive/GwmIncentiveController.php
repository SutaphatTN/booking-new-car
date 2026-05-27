<?php

namespace App\Http\Controllers\gwm_incentive;

use App\Exports\gwm_incentive\GwmIncentiveReportExport;
use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\TbGwmIncentive;
use App\Models\TbGwmKpi;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GwmIncentiveController extends Controller
{
    public static array $months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    ];

    public function index(Request $request)
    {
        $months       = self::$months;
        $currentMonth = (int) ($request->month ?? now()->month);
        $currentYear  = (int) ($request->year  ?? now()->year);

        $subcarmodels = TbSubcarmodel::with('model')
            ->where('active', 'active')
            ->orderBy('model_id')
            ->orderBy('name')
            ->get();

        $incentives = TbGwmIncentive::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->get()
            ->keyBy('subcarmodel_id');

        $kpi = TbGwmKpi::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();

        return view('gwm-incentive.view', compact(
            'months', 'currentMonth', 'currentYear', 'subcarmodels', 'incentives', 'kpi'
        ));
    }

    public function list(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $subcarmodels = TbSubcarmodel::with('model')
            ->where('active', 'active')
            ->orderBy('model_id')
            ->orderBy('name')
            ->get();

        $incentives = TbGwmIncentive::where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('subcarmodel_id');

        $data = $subcarmodels->map(function ($sub, $index) use ($incentives, $month, $year) {
            $modeName = $sub->model->Name_EN ?? '-';
            $carLabel = "{$modeName} {$sub->name}";
            $inc      = $incentives[$sub->id] ?? null;

            return [
                'No'           => $index + 1,
                'car'          => $carLabel,
                'fixed'        => $inc ? number_format($inc->fixed, 2) . '%'        : '-',
                'lt70'         => $inc ? number_format($inc->lt70, 2) . '%'         : '-',
                'gte70_lte85'  => $inc ? number_format($inc->gte70_lte85, 2) . '%'  : '-',
                'gt85_lte100'  => $inc ? number_format($inc->gt85_lte100, 2) . '%'  : '-',
                'gt100_lte120' => $inc ? number_format($inc->gt100_lte120, 2) . '%' : '-',
                'gte120'       => $inc ? number_format($inc->gte120, 2) . '%'       : '-',
                'max_val'        => $inc ? number_format($inc->max_val, 2) . '%'  : '-',
                'monthly_target' => $inc ? number_format($inc->monthly_target) . ' คัน' : '-',
                'Action'         => view('gwm-incentive.button', compact('inc', 'sub', 'month', 'year'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create(Request $request)
    {
        $models = TbCarmodel::orderBy('Name_EN')->get();

        $subcarmodels = TbSubcarmodel::with('model')
            ->where('active', 'active')
            ->orderBy('model_id')
            ->orderBy('name')
            ->get()
            ->groupBy('model_id');

        $months       = self::$months;
        $preSubId     = $request->sub_id;
        $preMonth     = (int) ($request->month ?? now()->month);
        $preYear      = (int) ($request->year  ?? now()->year);

        return view('gwm-incentive.create', compact(
            'models', 'subcarmodels', 'months', 'preSubId', 'preMonth', 'preYear'
        ));
    }

    public function checkExisting(Request $request)
    {
        $record = TbGwmIncentive::where('subcarmodel_id', $request->sub_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        return response()->json(['data' => $record]);
    }

    public function getSubModels($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function store(Request $request)
    {
        try {
            $exists = TbGwmIncentive::where('subcarmodel_id', $request->subcarmodel_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'มีข้อมูลรุ่นนี้ในเดือน/ปีนี้แล้ว',
                ], 422);
            }

            $record = TbGwmIncentive::create([
                'subcarmodel_id' => $request->subcarmodel_id,
                'month'          => $request->month,
                'year'           => $request->year,
                'fixed'          => $request->fixed        ?? 0,
                'lt70'           => $request->lt70         ?? 0,
                'gte70_lte85'    => $request->gte70_lte85  ?? 0,
                'gt85_lte100'    => $request->gt85_lte100  ?? 0,
                'gt100_lte120'   => $request->gt100_lte120 ?? 0,
                'gte120'         => $request->gte120        ?? 0,
                'max_val'        => $request->max_val       ?? 0,
                'monthly_target' => $request->monthly_target ?? 0,
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว', 'id' => $record->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function edit($id)
    {
        $incentive = TbGwmIncentive::with('subcarmodel.model')->findOrFail($id);
        $models    = TbCarmodel::orderBy('Name_EN')->get();
        $months    = self::$months;

        $subModels = TbSubcarmodel::where('model_id', $incentive->subcarmodel->model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('gwm-incentive.edit', compact('incentive', 'models', 'subModels', 'months'));
    }

    public function update(Request $request, $id)
    {
        try {
            $incentive = TbGwmIncentive::findOrFail($id);

            $duplicate = TbGwmIncentive::where('subcarmodel_id', $request->subcarmodel_id)
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->where('id', '!=', $id)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'มีข้อมูลรุ่นนี้ในเดือน/ปีนี้แล้ว',
                ], 422);
            }

            $incentive->update([
                'subcarmodel_id' => $request->subcarmodel_id,
                'month'          => $request->month,
                'year'           => $request->year,
                'fixed'          => $request->fixed        ?? 0,
                'lt70'           => $request->lt70         ?? 0,
                'gte70_lte85'    => $request->gte70_lte85  ?? 0,
                'gt85_lte100'    => $request->gt85_lte100  ?? 0,
                'gt100_lte120'   => $request->gt100_lte120 ?? 0,
                'gte120'         => $request->gte120        ?? 0,
                'max_val'        => $request->max_val       ?? 0,
                'monthly_target' => $request->monthly_target ?? 0,
            ]);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function upsertRow(Request $request)
    {
        try {
            $record = TbGwmIncentive::updateOrCreate(
                [
                    'subcarmodel_id' => $request->subcarmodel_id,
                    'month'          => $request->month,
                    'year'           => $request->year,
                ],
                [
                    'fixed'          => $request->fixed          ?? 0,
                    'lt70'           => $request->lt70           ?? 0,
                    'gte70_lte85'    => $request->gte70_lte85    ?? 0,
                    'gt85_lte100'    => $request->gt85_lte100    ?? 0,
                    'gt100_lte120'   => $request->gt100_lte120   ?? 0,
                    'gte120'         => $request->gte120         ?? 0,
                    'max_val'        => $request->max_val        ?? 0,
                    'monthly_target' => $request->monthly_target ?? 0,
                ]
            );
            return response()->json(['success' => true, 'message' => 'บันทึกเรียบร้อยแล้ว', 'id' => $record->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ======================== KPI ========================

    public function getKpi(Request $request)
    {
        $kpi = TbGwmKpi::where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        return response()->json(['data' => $kpi]);
    }

    public function storeKpi(Request $request)
    {
        try {
            $kpi = TbGwmKpi::updateOrCreate(
                ['month' => $request->month, 'year' => $request->year],
                [
                    'sale_kpi'       => $request->sale_kpi       ?? 0,
                    'ssi'            => $request->ssi            ?? 0,
                    'after_sale_kpi' => $request->after_sale_kpi ?? 0,
                    'csi'            => $request->csi            ?? 0,
                ]
            );

            return response()->json(['success' => true, 'message' => 'บันทึก KPI เรียบร้อยแล้ว', 'id' => $kpi->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // =====================================================

    public function destroy($id)
    {
        try {
            TbGwmIncentive::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ======================== REPORT ========================

    public function report(Request $request)
    {
        $months       = self::$months;
        $currentMonth = (int) ($request->month ?? now()->month);
        $currentYear  = (int) ($request->year  ?? now()->year);

        $data = $this->buildReportData($currentMonth, $currentYear);

        return view('gwm-incentive.report', array_merge($data, compact('months', 'currentMonth', 'currentYear')));
    }

    public function exportReport(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        $monthName = self::$months[$month] ?? $month;
        $filename  = "GWM_Incentive_{$monthName}_{$year}.xlsx";

        return Excel::download(new GwmIncentiveReportExport($month, $year), $filename);
    }

    public function buildReportData(int $month, int $year): array
    {
        $kpi = TbGwmKpi::where('month', $month)->where('year', $year)->first();
        $kpiTotal = $kpi
            ? ($kpi->sale_kpi + $kpi->ssi + $kpi->after_sale_kpi + $kpi->csi)
            : 0;

        $incentives = TbGwmIncentive::with('subcarmodel.model')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $salesRaw = DB::table('salecars')
            ->select('subModel_id', DB::raw('COUNT(*) as sale_count'), DB::raw('SUM(COALESCE(price_sub, 0)) as total_price'))
            ->where('brand', 2)
            ->where('con_status', 5)
            ->whereMonth('DeliveryDate', $month)
            ->whereYear('DeliveryDate', $year)
            ->whereNull('deleted_at')
            ->groupBy('subModel_id')
            ->get()
            ->keyBy('subModel_id');

        $rows = $incentives->map(function ($inc) use ($salesRaw, $kpiTotal) {
            $subId      = $inc->subcarmodel_id;
            $sale       = $salesRaw[$subId] ?? null;
            $count      = (int)   ($sale?->sale_count  ?? 0);
            $priceTotal = (float) ($sale?->total_price ?? 0);
            $target     = (int)   $inc->monthly_target;

            $achievePct = ($target > 0) ? ($count / $target * 100) : 0;

            $tierRate = match(true) {
                $achievePct < 70                        => (float) $inc->lt70,
                $achievePct >= 70 && $achievePct <= 85  => (float) $inc->gte70_lte85,
                $achievePct > 85  && $achievePct <= 100 => (float) $inc->gt85_lte100,
                $achievePct > 100 && $achievePct <= 120 => (float) $inc->gt100_lte120,
                default                                 => (float) $inc->gte120,
            };

            $totalPct  = $tierRate + (float) $inc->fixed + (float) $kpiTotal;
            $cappedPct = min($totalPct, (float) $inc->max_val);
            $amount    = $cappedPct / 100 * $priceTotal;

            return [
                'model_name'   => $inc->subcarmodel->model->Name_EN ?? '-',
                'sub_name'     => $inc->subcarmodel->name ?? '-',
                'count'        => $count,
                'target'       => $target,
                'achieve_pct'  => $achievePct,
                'price_total'  => $priceTotal,
                'fixed'        => (float) $inc->fixed,
                'lt70'         => (float) $inc->lt70,
                'gte70_lte85'  => (float) $inc->gte70_lte85,
                'gt85_lte100'  => (float) $inc->gt85_lte100,
                'gt100_lte120' => (float) $inc->gt100_lte120,
                'gte120'       => (float) $inc->gte120,
                'max_val'      => (float) $inc->max_val,
                'tier_rate'    => $tierRate,
                'kpi_total'    => (float) $kpiTotal,
                'total_pct'    => $totalPct,
                'capped_pct'   => $cappedPct,
                'amount'       => $amount,
            ];
        })->sortBy('model_name')->values();

        return compact('rows', 'kpi', 'kpiTotal', 'month', 'year');
    }
}
