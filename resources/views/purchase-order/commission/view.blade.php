@extends('layouts/contentNavbarLayout')
@section('title', 'Data Commission Sales')

@section('page-script')
@vite(['resources/assets/js/commission.js'])
@endsection

@section('page-style')
<style>
  /* แถว "เพิ่มยอดให้ฝ่ายขาย" ใต้แถวเลือกเดือน — คั่นด้วยเส้นประให้รู้ว่าเป็นคนละเรื่องกับตัวกรอง */
  .com-add-row {
    border-top: 1px dashed #dfe3ea;
    padding-top: 10px;
    margin-top: 2px;
  }
  /* select + ปุ่มติดกันใน input-group : ปิดมุมโค้งด้านที่ชนกัน (po-filter-bar บังคับโค้งทุกมุม) */
  .com-add-row .input-group > .form-select { border-radius: 8px 0 0 8px !important; }
  .com-add-row .input-group > .btn { border-radius: 0 8px 8px 0; }
</style>
@endsection

@section('content')
<div class="viewExportComModel"></div>
<div class="commissionDetailModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-coin-stack fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลค่าคอมมิชชั่นฝ่ายขาย</div>
          <div class="text-white mf-hd-sub">Commission Report</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label for="commissionMonth" class="mb-0 fw-semibold text-nowrap">
                <i class="bx bx-calendar me-1"></i> เลือกเดือน
              </label>
              <input type="month" id="commissionMonth" class="form-control form-control-sm" style="max-width:170px;"
                value="{{ now()->format('Y-m') }}">
            </div>
            {{-- ช่องกรอกเป้า : เซลล์ไม่ได้ตั้งเป้าเอง (endpoint ก็ล็อกไว้อยู่แล้ว) --}}
            @unless (in_array(auth()->user()->role, ['sale', 'lead_sale'], true) ||
                in_array((int) auth()->user()->brand, config('car_commission.brands_without_target', []), true))
              {{-- แบรนด์ที่ไม่มีเป้า (brand 3 คิดตามรุ่น / brand 4 คิดตามจำนวนคันล้วน) ไม่ต้องมีช่องกรอกเป้า --}}
              <div class="d-flex align-items-center gap-2">
                <label for="monthlyTarget" class="mb-0 fw-semibold text-nowrap">
                  <i class="bx bx-target-lock me-1"></i> เป้าเดือนนี้ (คัน)
                </label>
                <input type="number" min="0" id="monthlyTarget" class="form-control form-control-sm text-end"
                  style="max-width:90px;" placeholder="0">
                <button class="btn btn-outline-primary btn-sm" id="btnSaveTarget">
                  <i class="bx bx-save me-1"></i> บันทึกเป้า
                </button>
                <span id="targetStatus" class="small text-muted"></span>
              </div>
            @endunless
          </div>
          <button class="btn btn-warning btn-sm btnViewExportCom">
            <i class="bx bx-file me-1"></i> รายงานค่าคอม
          </button>

          {{-- ── เพิ่มยอดให้เซลล์ที่ไม่มีรถเดือนนี้ (เช่น คอมประดับยนต์หน้าร้าน / SSI) — บันทึกแล้วชื่อจะขึ้นในตาราง
               แยกเป็นแถวของตัวเองใต้เส้นประ ไม่ให้ไปเบียดแถวเลือกเดือน/เป้าจนตัดบรรทัดเละ --}}
          @if ($salePool->isNotEmpty())
            <div class="com-add-row w-100 d-flex align-items-center gap-2 flex-wrap">
              <span class="small text-muted">
                <i class="bx bx-user-plus me-1"></i> ฝ่ายขายที่ไม่มีรถเดือนนี้ แต่มียอดอื่น
              </span>
              <div class="input-group input-group-sm" style="max-width:380px;">
                {{-- ตัวเลือกเติมจาก JS ทุกครั้งที่ตารางโหลด (เฉพาะคนที่ยังไม่อยู่ในตารางของเดือนที่เลือก) --}}
                <select id="addSaleCommission" class="form-select">
                  <option value="">— เลือกฝ่ายขาย —</option>
                </select>
                <button type="button" class="btn btn-success text-nowrap" id="btnAddSaleCommission">
                  <i class="bx bx-plus me-1"></i> เพิ่มยอด
                </button>
              </div>
            </div>
          @endif
        </div>
        <div class="text-muted small mb-2">
          <i class="bx bx-info-circle me-1"></i> กดปุ่ม <span class="fw-semibold text-primary">“รายละเอียด / กรอกค่าคอม”</span> เพื่อดูรายชื่อลูกค้าและกรอกค่าคอมเพิ่มเติมของเดือนนั้น
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled commissionTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ฝ่ายขาย</th>
                <th>จำนวนคัน</th>
                <th>ยอดค่าคอมมิชชั่น</th>
                <th class="text-center">จัดการ</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<div id="commissionLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
