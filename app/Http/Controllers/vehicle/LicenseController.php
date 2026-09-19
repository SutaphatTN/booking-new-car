<?php

namespace App\Http\Controllers\vehicle;

use App\Exports\license\LoanLicExport;
use App\Exports\license\StockLicExport;
use App\Exports\license\SummaryLicExport;
use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\LicensePlateHistory;
use App\Models\LicensePlateLoan;
use App\Models\Salecar;
use App\Models\TbLicensePlate;
use App\Services\OneDriveService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
    $canManage = in_array($user->role, config('brand.plate_manage_roles', []));
    $brandNames = config('brand.names', []);

    // Vin ของรถทดลองขับที่ผูกกับป้าย (ผูกจากหน้า Car Order เมื่อประเภทการซื้อรถ = TestDrive)
    // ข้าม userAccess scope เพราะรถอาจถูกบันทึกโดยคนละสาขา/โซนกับคนที่เปิดหน้านี้
    // ป้ายทดลองขับไม่ได้ผูกใบขาย ช่อง "ลูกค้า" เลยว่าง — เอา Vin มาโชว์แทน (ชุดเดียวกับรายงาน Stock ป้ายแดง)
    $testDriveVins = CarOrder::withoutGlobalScopes(['userAccess', 'saleTeam'])
      ->whereIn('license_plate_id', $plates->pluck('id'))
      ->orderBy('id')
      ->get(['license_plate_id', 'vin_number'])
      ->groupBy('license_plate_id')
      ->map(fn($group) => $group->pluck('vin_number')->filter()->unique()->implode(', '));

    $data = $plates->values()->map(function ($p, $index) use ($histories, $userBrand, $canLoan, $canManage, $brandNames, $testDriveVins) {
      $history = $histories->get($p->id);
      $loan = $p->activeLoan;

      // ป้ายทดลองขับที่ผูกกับรถไว้ — เอา Vin มาโชว์ในช่องลูกค้า (ป้ายพวกนี้ไม่มีใบขาย จึงไม่มีลูกค้า)
      $testDriveVin = $p->plate_status_value === TbLicensePlate::STATUS_TEST_DRIVE
        ? $testDriveVins->get($p->id)
        : null;

      $prefix = $history?->saleCarLic?->customer?->prefix?->Name_TH ?? '';
      $first  = $history?->saleCarLic?->customer?->FirstName ?? '';
      $last   = $history?->saleCarLic?->customer?->LastName ?? '';
      $nameSale = $history?->saleCarLic?->saleUser?->name ?? '';

      // ── สถานะ ──
      // สถานะของตัวป้ายมาก่อนเสมอ — ป้ายที่ไม่ใช่ "ปกติ" หยิบมาใช้กับงานขายใหม่ไม่ได้
      // แยกสีตามลักษณะ : กันไว้ใช้เฉพาะทาง (ฟ้า) / ของไม่อยู่ในมือ (เหลือง) / ป้ายมีปัญหา (ดำ)
      if ($p->isBlocked()) {
        $badge = [
          TbLicensePlate::STATUS_TEST_DRIVE    => 'bg-primary',
          TbLicensePlate::STATUS_MOVING        => 'bg-info',
          TbLicensePlate::STATUS_WITH_CUSTOMER => 'bg-warning text-dark',
        ][$p->plate_status_value] ?? 'bg-dark';
        $status = '<span class="badge ' . $badge . '">' . e($p->plate_status_label) . '</span>';
      } elseif ($loan) {
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

      // ปุ่มแก้สถานะป้าย — role ใน plate_manage_roles และต้องมีทุกแถว (รวมป้ายว่าง)
      // เพราะใช้ mark สูญหาย/ชำรุด/ระหว่างติดตาม ซึ่งเกิดกับป้ายที่ไม่ได้ผูกงานขายก็ได้
      if ($canManage) {
        $action = '<button class="btn btn-icon btn-secondary btnEditPlateStatus" data-id="' . $p->id
          . '" data-number="' . e($p->number) . '" data-status="' . e($p->plate_status_value)
          . '" title="แก้ไขสถานะป้าย"><i class="bx bx-edit-alt"></i></button> '
          . ($action === '-' ? '' : $action);
      }

      return [
        'No' => $index + 1,
        'red' => $p->number,
        'owner' => $brandNames[$p->brand] ?? '-',
        'status' => $status,
        'FullName' => $p->is_used
          ? implode(' ', array_filter([$prefix, $first, $last]))
          : ($testDriveVin
            ? '<span class="text-muted" style="font-size:.8rem;" title="Vin รถทดลองขับ">' . e($testDriveVin) . '</span>'
            : '-'),
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

  /** สิทธิ์เพิ่มป้าย / แก้สถานะตัวป้าย — ดู config/brand.php : plate_manage_roles */
  private function ensureManageRole()
  {
    abort_unless(in_array(Auth::user()->role, config('brand.plate_manage_roles', [])), 403);
  }

  // เพิ่มป้ายแดงใหม่ (resource route: POST /license)
  public function store(Request $request)
  {
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

    $this->ensureManageRole();

    $request->validate([
      'number' => 'required|string|max:50',
      'brand' => 'required|integer',
    ]);

    // เก็บรูปแบบที่พิมพ์มาไว้ตามเดิม แต่บีบช่องว่างซ้ำให้เหลือช่องเดียว
    $number = preg_replace('/\s+/u', ' ', trim($request->number));

    if (!array_key_exists((int) $request->brand, config('brand.names', []))) {
      return response()->json(['success' => false, 'message' => 'แบรนด์ไม่ถูกต้อง'], 422);
    }

    // เลขป้ายจริงมีใบเดียว — ห้ามซ้ำข้ามทุกแบรนด์
    // เทียบแบบถอดช่องว่าง/ขีด/จุดออกก่อน กันเคส "ก 2250" กับ "ก2250" ที่เป็นป้ายเดียวกันแต่พิมพ์คนละแบบ
    $existing = TbLicensePlate::withoutGlobalScope('brandAccess')
      ->whereRaw(
        "REPLACE(REPLACE(REPLACE(number, ' ', ''), '-', ''), '.', '') = ?",
        [preg_replace('/[\s\-.]+/u', '', $number)]
      )
      ->first();
    if ($existing) {
      $ownerName = config("brand.names.{$existing->brand}", 'แบรนด์อื่น');
      return response()->json([
        'success' => false,
        'message' => "เลขป้าย {$existing->number} มีอยู่แล้ว (ของ {$ownerName})"
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

  /**
   * แก้สถานะของตัวป้าย — role ใน plate_manage_roles (ดู TbLicensePlate::PLATE_STATUSES)
   * ทุกสถานะยกเว้น "ปกติ" ทำให้ป้ายถูกยืมหรือเลือกผูกงานขายใหม่ไม่ได้
   */
  public function updateStatus(Request $request, $id)
  {
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

    $this->ensureManageRole();

    $request->validate([
      'plate_status' => ['required', 'string', Rule::in(array_keys(TbLicensePlate::PLATE_STATUSES))],
    ]);

    // ข้าม brand scope — สถานะเป็นเรื่องของ "ตัวป้าย" ไม่ใช่ของแบรนด์ที่ถืออยู่
    // ป้ายที่ยืมไปอาจชำรุด/สูญหายระหว่างอยู่กับผู้ยืม หรือค้างที่ลูกค้า จึงต้องแก้ได้ทุกกรณี
    // ไม่มีด่านกันไว้แล้ว — ความรับผิดชอบอยู่ที่ activity_logs (เก็บว่าใคร แบรนด์ไหน เปลี่ยนจากอะไรเป็นอะไร)
    $plate = TbLicensePlate::withoutGlobalScope('brandAccess')->find($id);
    if (!$plate) {
      return response()->json(['success' => false, 'message' => 'ไม่พบป้ายแดง'], 404);
    }

    $plate->update(['plate_status' => $request->plate_status]);

    return response()->json([
      'success' => true,
      'message' => 'เปลี่ยนสถานะป้าย ' . $plate->number . ' เป็น "' . $plate->plate_status_label . '" เรียบร้อยแล้ว',
    ]);
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
      ->usable()   // ตัดป้ายสูญหาย/ชำรุด/ระหว่างติดตามออก
      ->whereDoesntHave('loans', fn($q) => $q->whereNull('return_date'))
      ->orderBy('number')
      ->get(['id', 'number']);

    return response()->json(['data' => $plates]);
  }

  public function storeLoan(Request $request)
  {
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

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
    if ($plate->isBlocked()) {
      return response()->json([
        'success' => false,
        'message' => 'ป้ายนี้อยู่ในสถานะ "' . $plate->plate_status_label . '" ไม่สามารถยืมได้',
      ], 422);
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
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

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
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

    try {
      $lic = LicensePlateHistory::findOrFail($id);
      $data = $request->except(['_token', '_method']);

      // filled() ไม่ใช่ truthy — ไม่งั้นกรอก "0" (คืนเงิน 0 บาท ซึ่งเป็นเคสที่มีจริง)
      // จะกลายเป็น null แล้วด่านก่อนยืนยันการจ่ายเงินจะมองว่ายังไม่ได้กรอก
      $data['refund_amount'] = $request->filled('refund_amount')
        ? str_replace(',', '', $request->refund_amount)
        : null;

      $data['UserInsert'] = Auth::id();

      $data['license_red_front'] = $request->has('license_red_front') ? 1 : 0;
      $data['license_red_back']  = $request->has('license_red_back') ? 1 : 0;
      $data['license_red_book']  = $request->has('license_red_book') ? 1 : 0;

      $lic->update($data);

      // "วันที่ลูกค้าจ่ายเงิน (ค่าป้ายแดง)" อยู่บนใบขาย ไม่ใช่ตารางประวัติป้าย — เขียนกลับไปที่ใบขายเอง
      // (คีย์นี้หลุดเข้า $data ด้วย แต่ไม่อยู่ใน fillable ของ LicensePlateHistory จึงถูกมองข้าม)
      // ส่งค่าว่างมา = ล้างวันทิ้ง ; ใบที่ยังไม่ผูกใบขาย ช่องถูก disable อยู่แล้วจึงไม่มีคีย์นี้ส่งมา
      if ($request->has('red_license_pay_date') && $lic->saleCarLic) {
        $lic->saleCarLic->update([
          'red_license_pay_date' => $request->red_license_pay_date ?: null,
        ]);
      }

      // หลักฐานการโอนเงินค่าป้ายแดง — เก็บบนใบขาย (salecars.red_license_slip_url) ไฟล์ชุดเดียว
      // กับหน้าใบจอง/หน้าประวัติ ; โฟลเดอร์ปลายทางต้องตรงกันด้วย ไม่งั้นไฟล์ของเรื่องเดียวกันจะกระจาย 2 ที่
      if ($request->hasFile('red_license_slips') && $lic->saleCarLic) {
        $sale = $lic->saleCarLic;
        $payCustomer = $sale->customer;
        $payFolderName = ($payCustomer->id ?? $sale->id) . '-' . ($payCustomer->FirstName ?? 'unknown');
        $payBrandName = Auth::user()->brandInfo->name ?? 'Other';
        $payFolder = "New Car/{$payBrandName}/ป้ายแดง/หลักฐานลูกค้าโอนเงิน/{$payFolderName}";

        $payDrive = new OneDriveService();
        $paySlips = is_array($sale->red_license_slip_url) ? $sale->red_license_slip_url : [];

        foreach ($request->file('red_license_slips') as $index => $file) {
          $payFileName = 'red_plate_slip_' . $sale->id . '_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
          $paySlips[] = [
            'url'  => $payDrive->upload($file->getRealPath(), $payFileName, $payFolder),
            'name' => $file->getClientOriginalName(),
          ];
        }

        $sale->update(['red_license_slip_url' => $paySlips]);
      }

      // สลิปคืนเงินลูกค้า → OneDrive : New Car/{แบรนด์}/ป้ายแดง/หลักฐานคืนเงินลูกค้า/{id-ชื่อลูกค้า}
      // ต่อท้ายของเดิมเสมอ ไม่ลบไฟล์เก่าทิ้ง (ลบทีละไฟล์ผ่านปุ่มกากบาทบนการ์ด)
      if ($request->hasFile('refund_slips')) {
        $customer = $lic->saleCarLic?->customer;
        $folderName = ($customer->id ?? $lic->id) . '-' . ($customer->FirstName ?? 'unknown');
        $brandName = Auth::user()->brandInfo->name ?? 'Other';
        $folder = "New Car/{$brandName}/ป้ายแดง/หลักฐานคืนเงินลูกค้า/{$folderName}";

        $oneDrive = new OneDriveService();
        $slips = is_array($lic->refund_slip_url) ? $lic->refund_slip_url : [];

        foreach ($request->file('refund_slips') as $index => $file) {
          $fileName = 'refund_slip_' . $lic->id . '_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
          $slips[] = [
            'url'  => $oneDrive->upload($file->getRealPath(), $fileName, $folder),
            'name' => $file->getClientOriginalName(),
          ];
        }

        $lic->update(['refund_slip_url' => $slips]);
      }

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

  /**
   * เปิดดูสลิปคืนเงินลูกค้า — ไฟล์อยู่บน OneDrive ที่แชร์ระดับองค์กร
   * ต้องผ่าน proxy เพราะลิงก์ตรงเปิดได้เฉพาะคนที่ล็อกอิน OneDrive ขององค์กรอยู่
   * เปิดได้เฉพาะไฟล์ที่ผูกกับประวัติป้ายแถวนั้นจริง (กันเอา url อะไรก็ได้มายิงผ่าน endpoint นี้)
   */
  public function proxyRefundSlip(Request $request, $id, $filename = null)
  {
    $lic = LicensePlateHistory::findOrFail($id);
    $shareUrl = (string) $request->input('url');

    $allowed = collect($lic->refund_slip_url ?? [])
      ->contains(fn($f) => (is_array($f) ? $f['url'] ?? '' : $f) === $shareUrl);

    abort_unless($allowed, 403);

    try {
      ['url' => $downloadUrl, 'name' => $name] = (new OneDriveService())->getDownloadInfo($shareUrl);

      $response = (new Client(['allow_redirects' => true]))->get($downloadUrl);

      return response($response->getBody()->getContents(), 200, [
        'Content-Type'        => $response->getHeader('Content-Type')[0] ?? 'application/octet-stream',
        'Content-Disposition' => "inline; filename=\"{$name}\"",
        'Cache-Control'       => 'private, max-age=3600',
      ]);
    } catch (\Exception $e) {
      abort(404);
    }
  }

  /** ลบสลิปคืนเงินลูกค้าทีละไฟล์ (ลบออกจากรายการ ไฟล์บน OneDrive ยังอยู่เหมือนไฟล์แนบที่อื่นในระบบ) */
  public function deleteRefundSlip(Request $request, $id)
  {
    $lic = LicensePlateHistory::findOrFail($id);

    $index = (int) $request->input('index');
    $slips = is_array($lic->refund_slip_url) ? $lic->refund_slip_url : [];

    if (!isset($slips[$index])) {
      return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์'], 404);
    }

    array_splice($slips, $index, 1);
    $lic->update(['refund_slip_url' => $slips ?: null]);

    return response()->json(['success' => true, 'remaining' => count($slips)]);
  }

  public function approveFinance(Request $request)
  {
    // role ดูอย่างเดียว (insurance_reg) แก้ไขอะไรในเมนูทะเบียนไม่ได้เลย — ปุ่มถูกซ่อนแล้ว ตรงนี้กันยิง endpoint ตรง
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);

    $history = LicensePlateHistory::findOrFail($request->id);

    // ── ด่านก่อนยืนยันการจ่ายเงินจริง (กดแล้วย้อนไม่ได้ + ปลดป้ายคืนสต็อกทันที) ──
    // กติกาอยู่ที่ LicensePlateHistory::approveFinanceMissing() — ปุ่มในตารางใช้ชุดเดียวกัน
    $missing = $history->approveFinanceMissing();

    if ($missing) {
      return response()->json([
        'success' => false,
        'message' => 'กรุณากรอกข้อมูลในหน้า "แก้ไขข้อมูลป้ายแดง" ให้ครบก่อน : ' . implode(', ', $missing),
      ], 422);
    }

    $history->update([
      'finance_approved' => Auth::id(),
      'finance_approved_date' => now()
    ]);

    // ปลดป้ายคืนสต็อกเฉพาะรายการที่ "ยังถือป้ายอยู่"
    // รายการที่กด "คืนป้ายก่อน" ไปแล้ว ป้ายถูกปลดไปตั้งแต่ตอนนั้นและอาจถูกผูกกับลูกค้ารายใหม่แล้ว
    // ถ้าปลดซ้ำตรงนี้ = ไปแย่งป้ายจากเจ้าของคนใหม่
    if ($history->licenseLic && !$history->plate_returned_at) {
      $history->licenseLic->update([
        'is_used' => 0
      ]);
    }

    return response()->json(['success' => true]);
  }

  /** role ที่กด "ยืนยันการจ่ายเงินจริง" ได้ (ปิดรายการเงิน กดแล้วย้อนไม่ได้) */
  public const FINANCE_ROLES = ['account', 'admin', 'audit', 'audit_lead', 'audit_dp', 'gm'];

  /**
   * role ที่กด "คืนป้ายก่อน (ยังไม่ปิดเงิน)" ได้ — กว้างกว่า FINANCE_ROLES เพราะเป็นเรื่องหมุนป้าย
   * ไม่ใช่การปิดเงิน (รายการยังค้างไปโผล่หน้า "ค้างคืนเงินป้ายแดง" เหมือนเดิม)
   * 2026-09-19: เพิ่ม md ตามที่เจ้าของสั่ง — md ยังปิดเงินเองไม่ได้
   */
  public const RETURN_PLATE_EARLY_ROLES = [...self::FINANCE_ROLES, 'md'];

  /**
   * คืนป้ายก่อน (ยังไม่ปิดเงิน)
   * เคสจริง : ลูกค้ายังไม่มารับเงินคืน/ยังไม่ถึงรอบจ่าย แต่ป้ายต้องเอาไปผูกกับลูกค้ารายใหม่แล้ว
   * ทำ 3 อย่าง : ปลดป้ายคืนสต็อก + ล้างป้ายออกจากใบขายเดิม + ประทับว่าใครคืนเมื่อไรเพราะอะไร
   * รายการจะยังค้างอยู่ (finance_approved = null) ให้ไปตามเก็บที่หน้า "ค้างคืนเงินป้ายแดง"
   */
  public function returnPlateEarly(Request $request, $id)
  {
    abort_if(Auth::user()->isRegistrationViewOnly(), 403);
    abort_unless(in_array(Auth::user()->role, self::RETURN_PLATE_EARLY_ROLES, true), 403);

    $request->validate([
      'note' => ['required', 'string', 'max:255'],
    ], [
      'note.required' => 'กรุณาระบุเหตุผลที่คืนป้ายก่อนปิดเงิน',
    ]);

    $history = LicensePlateHistory::withoutGlobalScope('brandAccess')->findOrFail($id);

    if ($history->finance_approved) {
      return response()->json(['success' => false, 'message' => 'รายการนี้ปิดเงินไปแล้ว ไม่ต้องคืนป้ายซ้ำ'], 422);
    }

    if ($history->plate_returned_at) {
      return response()->json(['success' => false, 'message' => 'รายการนี้คืนป้ายไปแล้ว'], 422);
    }

    DB::transaction(function () use ($history, $request) {
      $history->update([
        'plate_returned_at'  => now(),
        'plate_returned_by'  => Auth::id(),
        'plate_return_note'  => trim($request->note),
      ]);

      // ปลดป้ายคืนสต็อก — ข้าม brand scope เผื่อเป็นป้ายที่ยืมมาจากแบรนด์อื่น
      // ใช้ updateLogged ไม่ใช่ ->update() ตรง ๆ : query builder update ไม่ผ่าน model event
      // ประวัติใน activity_logs จะหาย ทั้งที่การปลดป้ายเป็นเรื่องที่ต้องตามหลังได้
      if ($history->licenseID) {
        TbLicensePlate::updateLogged(
          fn($q) => $q->withoutGlobalScope('brandAccess')->whereKey($history->licenseID),
          ['is_used' => 0]
        );
      }

      // ล้างป้ายออกจากใบขายเดิม — ของจริงไม่ได้อยู่กับใบนี้แล้ว และกันเคสมีคนแก้ใบนี้ทีหลัง
      // แล้ว syncRedPlate ไปปลด is_used ของป้ายที่ลูกค้ารายใหม่ถืออยู่
      // (ประวัติว่าใบนี้เคยถือป้ายไหน ยังอยู่ครบใน license_plate_history)
      if ($history->saleID) {
        Salecar::updateLogged(
          fn($q) => $q->withoutGlobalScopes()
            ->whereKey($history->saleID)
            ->where('red_license', $history->licenseID),
          ['red_license' => null]
        );
      }
    });

    return response()->json([
      'success' => true,
      'message' => 'คืนป้ายเรียบร้อยแล้ว — รายการนี้ยังค้างปิดเงิน ดูได้ที่เมนู "ค้างคืนเงินป้ายแดง"',
    ]);
  }

  /** หน้า "ค้างคืนเงินป้ายแดง" — รายการที่คืนป้ายไปก่อนแล้วแต่ยังไม่ได้ปิดเงิน */
  public function pendingRefund()
  {
    return view('number_register.license.pending-refund');
  }

  public function listPendingRefund()
  {
    // ประวัติป้ายแดงแชร์ทั้งกลุ่ม brand (ป้ายเป็นกองเดียวกัน) แต่หน้านี้เป็นการไล่เก็บเงินของใบขาย
    // จึงต้องเห็นเฉพาะ brand ที่ตัวเองทำงานอยู่ ไม่งั้น Lepas/Wuling เห็นรายการค้างของ Mitsu ปนมา
    $rows = LicensePlateHistory::pendingRefund()
      ->ownBrandOnly()
      ->with([
        'saleCarLic' => fn($q) => $q->withoutGlobalScope('userAccess')->with(['customer.prefix', 'saleUser']),
        'licenseLic' => fn($q) => $q->withoutGlobalScope('brandAccess'),
        'plateReturnUser',
      ])
      ->orderBy('plate_returned_at')
      ->get();

    $data = $rows->map(function ($r, $i) {
      $cus = $r->saleCarLic?->customer;
      $name = trim(($cus?->prefix?->Name_TH ?? '') . ' ' . ($cus?->FirstName ?? '') . ' ' . ($cus?->LastName ?? ''));
      $missing = $r->approveFinanceMissing();

      return [
        'No'        => $i + 1,
        'customer'  => $name ?: '-',
        'plate'     => $r->licenseLic?->number ?? '-',
        'vin'       => $r->saleCarLic?->carOrder?->vin_number ?? '-',
        'sale'      => $r->saleCarLic?->saleUser?->display_name ?? '-',
        'returned'  => $r->format_plate_returned_at ?? '-',
        'returnBy'  => $r->plateReturnUser?->name ?? '-',
        'note'      => $r->plate_return_note ?? '-',
        'missing'   => $missing
          ? '<span class="badge bg-label-warning" title="' . e(implode(', ', $missing)) . '">ยังขาด ' . count($missing) . ' อย่าง</span>'
          : '<span class="badge bg-label-success">ข้อมูลครบ</span>',
        'Action'    => view('number_register.license.pending-refund-button', ['history' => $r])->render(),
      ];
    });

    return response()->json(['data' => $data]);
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
