<?php

namespace App\Http\Controllers\vehicle;

use App\Exports\license\LoanLicExport;
use App\Exports\license\StockLicExport;
use App\Exports\license\SummaryLicExport;
use App\Http\Controllers\Controller;
use App\Models\LicensePlateHistory;
use App\Models\LicensePlateLoan;
use App\Models\TbLicensePlate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;

class LicenseController extends Controller
{
  public function index()
  {
    $lic = LicensePlateHistory::all();
    return view('number_register.license.view', compact('lic'));
  }

  public function listLicense()
  {
    // scope = ป้ายของแบรนด์ตัวเอง + ป้ายที่แบรนด์ตัวเองยืมอยู่ (ดู TbLicensePlate::booted)
    $plates = TbLicensePlate::with('activeLoan')->orderBy('number')->get();

    // ประวัติที่ยังไม่ปิด — ดึงตรง (ข้าม brand scope) เพราะป้ายที่เห็นอาจถูกอีกแบรนด์ใช้อยู่
    // ผ่านการยืม แต่สิทธิ์ "จัดการ" (ปุ่ม Action) ยังจำกัดเฉพาะแบรนด์ที่ผูกป้ายด้านล่าง
    $histories = LicensePlateHistory::withoutGlobalScope('brandAccess')
      ->whereIn('licenseID', $plates->pluck('id'))
      ->whereNull('finance_approved')
      ->with(['saleCarLic' => function ($q) {
        $q->withoutGlobalScope('userAccess')->with(['customer.prefix', 'saleUser']);
      }])
      ->orderBy('id')
      ->get()
      ->groupBy('licenseID')
      ->map(fn($group) => $group->last());

    $user = Auth::user();
    $userBrand = $user->brand;
    $canLoan = in_array($user->role, config('brand.plate_loan_roles', []));
    $brandNames = config('brand.names', []);

    $data = $plates->values()->map(function ($p, $index) use ($histories, $userBrand, $canLoan, $brandNames) {
      $history = $histories->get($p->id);
      $loan = $p->activeLoan;

      $prefix = $history?->saleCarLic?->customer?->prefix?->Name_TH ?? '';
      $first  = $history?->saleCarLic?->customer?->FirstName ?? '';
      $last   = $history?->saleCarLic?->customer?->LastName ?? '';
      $nameSale = $history?->saleCarLic?->saleUser?->name ?? '';

      // ── สถานะยืม/ใช้งาน ──
      if ($loan) {
        $isBorrower = $userBrand && $loan->borrower_brand == $userBrand;
        $status = $isBorrower
          ? '<span class="badge bg-info">ยืมจาก ' . e($brandNames[$loan->owner_brand] ?? 'แบรนด์อื่น') . ' (ยืม ' . $loan->format_borrow_date . ')</span>'
          : '<span class="badge bg-warning text-dark">' . e($brandNames[$loan->borrower_brand] ?? 'แบรนด์อื่น') . ' ยืมอยู่ (ยืม ' . $loan->format_borrow_date . ')</span>';
      } else {
        $status = $p->is_used
          ? '<span class="badge bg-danger">ใช้งาน</span>'
          : '<span class="badge bg-success">ว่าง</span>';
      }

      // ป้ายถูกผูกโดยการขายของอีกแบรนด์ (ผ่านการยืม) → โชว์ข้อมูลได้ แต่ห้ามจัดการ
      $isOtherBrand = $history && $p->is_used && $userBrand && $history->brand != $userBrand;

      $action = $isOtherBrand
        ? '<span class="badge bg-secondary">ใช้งานโดย ' . e(config("brand.names.{$history->brand}", 'แบรนด์อื่น')) . '</span>'
        : (($history && $p->is_used)
          ? view('number_register.license.button', [
            'plate' => $p,
            'history' => $history
          ])->render()
          : '-');

      // ปุ่มคืนป้าย: ป้ายที่ยืมค้าง + user มีสิทธิ์ (ฝั่งยืมหรือเจ้าของ)
      // ถ้าป้ายยังผูกงานขาย (is_used) โชว์ปุ่มไว้แต่กดแล้ว JS จะเตือนให้ปิดงานขายก่อน
      if ($loan && $canLoan && (!$userBrand || in_array($userBrand, [$loan->borrower_brand, $loan->owner_brand]))) {
        $action = '<button class="btn btn-icon btn-danger btnReturnPlate" data-id="' . $loan->id
          . '" data-number="' . e($p->number) . '" data-borrow="' . $loan->format_borrow_date
          . '" data-inuse="' . ($p->is_used ? 1 : 0)
          . '" title="คืนป้าย"><i class="bx bx-undo"></i></button> '
          . ($action === '-' ? '' : $action);
      }

      return [
        'No' => $index + 1,
        'red' => $p->number,
        'owner' => $brandNames[$p->brand] ?? '-',
        'status' => $status,
        'FullName' => $p->is_used
          ? implode(' ', array_filter([$prefix, $first, $last]))
          : '-',
        'sale' => $p->is_used
          ? $nameSale
          : '-',
        'date' => $p->is_used
          ? ($history?->saleCarLic?->format_delivery_date ?? '-')
          : '-',
        'Action' => $action ?: '-',
      ];
    });

    return response()->json(['data' => $data]);
  }

  // เพิ่มป้ายแดงใหม่ — admin เท่านั้น (resource route: POST /license)
  public function store(Request $request)
  {
    abort_unless(Auth::user()->role === 'admin', 403);

    $request->validate([
      'number' => 'required|string|max:50',
      'brand' => 'required|integer',
    ]);

    $number = trim($request->number);

    if (!array_key_exists((int) $request->brand, config('brand.names', []))) {
      return response()->json(['success' => false, 'message' => 'แบรนด์ไม่ถูกต้อง'], 422);
    }

    // เลขป้ายจริงมีใบเดียว — ห้ามซ้ำข้ามทุกแบรนด์
    $existing = TbLicensePlate::withoutGlobalScope('brandAccess')
      ->where('number', $number)
      ->first();
    if ($existing) {
      $ownerName = config("brand.names.{$existing->brand}", 'แบรนด์อื่น');
      return response()->json([
        'success' => false,
        'message' => "เลขป้าย {$number} มีอยู่แล้ว (ของ {$ownerName})"
      ], 422);
    }

    TbLicensePlate::create([
      'number' => $number,
      'is_used' => 0,
      'brand' => (int) $request->brand,
      'userZone' => Auth::user()->userZone ?? null,
      'branch' => Auth::user()->branch ?? null,
    ]);

    return response()->json(['success' => true, 'message' => "เพิ่มป้ายแดง {$number} เรียบร้อยแล้ว"]);
  }

  // ── ยืม-คืนป้ายแดงข้ามแบรนด์ ──

  private function ensureLoanRole()
  {
    abort_unless(in_array(Auth::user()->role, config('brand.plate_loan_roles', [])), 403);
  }

  // ป้ายว่างของแบรนด์เจ้าของที่จะไปยืม (ยังไม่ถูกใช้ + ไม่ติดยืมค้าง)
  public function loanOptions(Request $request)
  {
    $this->ensureLoanRole();

    $plates = TbLicensePlate::withoutGlobalScope('brandAccess')
      ->where('brand', (int) $request->brand)
      ->where('is_used', 0)
      ->whereDoesntHave('loans', fn($q) => $q->whereNull('return_date'))
      ->orderBy('number')
      ->get(['id', 'number']);

    return response()->json(['data' => $plates]);
  }

  public function storeLoan(Request $request)
  {
    $this->ensureLoanRole();

    $request->validate([
      'license_plate_id' => 'required|integer',
      'borrow_date' => 'required|date',
    ]);

    // user มีแบรนด์ = ยืมเข้าแบรนด์ตัวเอง / admin (ไม่มีแบรนด์) เลือกแบรนด์ที่ยืมเอง
    $borrowerBrand = Auth::user()->brand ?: (int) $request->borrower_brand;
    if (!$borrowerBrand) {
      return response()->json(['success' => false, 'message' => 'กรุณาเลือกแบรนด์ที่ยืม'], 422);
    }

    $plate = TbLicensePlate::withoutGlobalScope('brandAccess')->find($request->license_plate_id);

    if (!$plate) {
      return response()->json(['success' => false, 'message' => 'ไม่พบป้ายแดง'], 404);
    }
    if ($plate->brand == $borrowerBrand) {
      return response()->json(['success' => false, 'message' => 'ป้ายนี้เป็นของแบรนด์ที่ยืมอยู่แล้ว'], 422);
    }
    if ($plate->is_used) {
      return response()->json(['success' => false, 'message' => 'ป้ายนี้ถูกใช้งานอยู่'], 422);
    }
    if ($plate->loans()->whereNull('return_date')->exists()) {
      return response()->json(['success' => false, 'message' => 'ป้ายนี้ถูกยืมอยู่แล้ว'], 422);
    }

    LicensePlateLoan::create([
      'license_plate_id' => $plate->id,
      'owner_brand' => $plate->brand,
      'borrower_brand' => $borrowerBrand,
      'borrow_date' => $request->borrow_date,
      'note' => $request->note,
      'borrowed_by' => Auth::id(),
    ]);

    return response()->json(['success' => true, 'message' => 'บันทึกการยืมป้ายเรียบร้อยแล้ว']);
  }

  public function returnLoan(Request $request, $id)
  {
    $this->ensureLoanRole();

    $request->validate(['return_date' => 'required|date']);

    $loan = LicensePlateLoan::whereNull('return_date')->find($id);
    if (!$loan) {
      return response()->json(['success' => false, 'message' => 'ไม่พบรายการยืมที่ค้างอยู่'], 404);
    }

    $user = Auth::user();
    if ($user->brand && !in_array($user->brand, [$loan->borrower_brand, $loan->owner_brand])) {
      return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์คืนป้ายรายการนี้'], 403);
    }

    $plate = TbLicensePlate::withoutGlobalScope('brandAccess')->find($loan->license_plate_id);
    if ($plate && $plate->is_used) {
      return response()->json(['success' => false, 'message' => 'ป้ายยังผูกกับงานขายที่ยังไม่ยืนยันจ่ายเงิน ไม่สามารถคืนได้'], 422);
    }

    if (Carbon::parse($request->return_date)->lt(Carbon::parse($loan->borrow_date))) {
      return response()->json(['success' => false, 'message' => 'วันที่คืนต้องไม่ก่อนวันที่ยืม'], 422);
    }

    $loan->update([
      'return_date' => $request->return_date,
      'returned_by' => Auth::id(),
    ]);

    return response()->json(['success' => true, 'message' => 'คืนป้ายเรียบร้อยแล้ว เจ้าของนำกลับไปใช้ได้']);
  }

  public function viewMore($id)
  {
    $lic = LicensePlateHistory::with([
      'licenseLic',
      'saleCarLic.customer.prefix',
      'saleCarLic.saleUser',
      'saleCarLic.vehicleLicense.provincesV',
      'saleCarLic.accessories'
    ])->find($id);

    return view('number_register.license.view-more', compact('lic'));
  }

  public function edit($id)
  {
    $lic = LicensePlateHistory::with([
      'licenseLic',
      'saleCarLic.customer.prefix',
      'saleCarLic.saleUser',
      'saleCarLic.vehicleLicense.provincesV',
      'saleCarLic.accessories'
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
    return Excel::download(new StockLicExport($request), ExportFilename::withBrand('license-stock.xlsx'));
  }

  // ประวัติยืม-คืนป้ายแดงทั้งหมด แยก sheet ตามแบรนด์ — เฉพาะ admin/audit_internal
  public function exportLicLoan()
  {
    $this->ensureLoanRole();

    return Excel::download(new LoanLicExport, ExportFilename::withBrand('license-loan-history.xlsx'));
  }

  public function viewExportLicense()
    {
        return view('number_register.license.report.view');
    }

    public function exportLicSummary(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new SummaryLicExport($fromDate, $toDate), ExportFilename::withBrand('license-history.xlsx'));
    }
}
