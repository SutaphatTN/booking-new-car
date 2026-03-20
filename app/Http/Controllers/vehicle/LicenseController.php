<?php

namespace App\Http\Controllers\vehicle;

use App\Exports\license\StockLicExport;
use App\Exports\license\SummaryLicExport;
use App\Http\Controllers\Controller;
use App\Models\LicensePlateHistory;
use App\Models\TbLicensePlate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LicenseController extends Controller
{
  public function index()
  {
    $lic = LicensePlateHistory::all();
    return view('number_register.license.view', compact('lic'));
  }

  public function listLicense()
  {
    $plates = TbLicensePlate::with([
      'currentHistory.saleCarLic.customer.prefix',
      'currentHistory.saleCarLic.saleUser'
    ])->get();

    $data = $plates->map(function ($p, $index) {
      $history = $p->currentHistory;
      $prefix = $history?->saleCarLic?->customer?->prefix?->Name_TH ?? '';
      $first  = $history?->saleCarLic?->customer?->FirstName ?? '';
      $last   = $history?->saleCarLic?->customer?->LastName ?? '';
      $nameSale = $history?->saleCarLic?->saleUser?->name ?? '';

      return [
        'No' => $index + 1,
        'FullName' => $p->is_used
          ? implode(' ', array_filter([$prefix, $first, $last]))
          : '-',
        'sale' => $p->is_used
          ? $nameSale
          : '-',
        'red' => $p->number,
        'date' => $p->is_used
          ? ($history?->saleCarLic?->format_delivery_date ?? '-')
          : '-',
        'Action' => $history
          ? view('number_register.license.button', [
            'plate' => $p,
            'history' => $history
          ])->render()
          : '-'
      ];
    });

    return response()->json(['data' => $data]);
  }

  public function viewMore($id)
  {
    $lic = LicensePlateHistory::with([
      'licenseLic',
      'saleCarLic.customer.prefix',
      'saleCarLic.saleUser'
    ])->find($id);

    return view('number_register.license.view-more', compact('lic'));
  }

  public function edit($id)
  {
    $lic = LicensePlateHistory::with([
      'licenseLic',
      'saleCarLic.customer.prefix',
      'saleCarLic.saleUser',
      'saleCarLic.vehicleLicense'
    ])->findOrFail($id);

    return view('number_register.license.edit', compact('lic'));
  }

  public function update(Request $request, $id)
  {
    try {
      $lic = LicensePlateHistory::findOrFail($id);
      $data = $request->except(['_token', '_method']);

      $data['refund_amount'] = $request->refund_amount
        ? str_replace(',', '', $request->refund_amount)
        : null;

      $data['UserInsert'] = Auth::id();

      $data['license_red_front'] = $request->has('license_red_front') ? 1 : 0;
      $data['license_red_back']  = $request->has('license_red_back') ? 1 : 0;
      $data['license_red_book']  = $request->has('license_red_book') ? 1 : 0;

      $lic->update($data);

      return response()->json([
        'success' => true,
        'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
      ], 500);
    }
  }

  public function approveFinance(Request $request)
  {
    $history = LicensePlateHistory::findOrFail($request->id);

    $history->update([
      'finance_approved' => Auth::id(),
      'finance_approved_date' => now()
    ]);

    if ($history->licenseLic) {
      $history->licenseLic->update([
        'is_used' => 0
      ]);
    }

    return response()->json(['success' => true]);
  }

  public function exportLicStock(Request $request)
  {
    return Excel::download(new StockLicExport($request), 'license-stock.xlsx');
  }

  public function viewExportLicense()
    {
        return view('number_register.license.report.view');
    }

    public function exportLicSummary(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new SummaryLicExport($fromDate, $toDate), 'license-history.xlsx');
    }
}
