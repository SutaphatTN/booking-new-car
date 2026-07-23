@extends('layouts/contentNavbarLayout')
@section('title', 'Data License Plate')

@section('page-script')
  @vite(['resources/assets/js/license.js'])
@endsection

@section('content')
  {{-- ไอคอนในปุ่ม Action เป็นสีขาว — ต้องอยู่ระดับหน้า ไม่ใช่ใน partial ของแถว
       (DataTables ถอดแถวที่ไม่ตรงคำค้นออกจาก DOM style ที่ฝังในแถวจะหายไปด้วย) --}}
  <style>
    .licenseTable .btn-icon i {
      color: #fff;
    }
  </style>

  <div class="viewMoreLicenseModel"></div>
  <div class="editLicenseModel"></div>
  <div class="viewExportLicenseAllModel"></div>

  {{-- ตัวเลือกสถานะป้าย (admin เท่านั้น) — ให้ JS อ่านไปสร้าง dropdown ตอนกดแก้สถานะ --}}
  @if (auth()->user()->role === 'admin')
    <div id="plateStatusOptions" class="d-none" data-options='@json(\App\Models\TbLicensePlate::PLATE_STATUSES)'></div>
  @endif
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-id-card fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ข้อมูลป้ายแดง</div>
            <div class="text-white mf-hd-sub">License Plate</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Action bar ── --}}
          @php
            $canLoan = in_array(auth()->user()->role, config('brand.plate_loan_roles', []));
            $userBrand = auth()->user()->brand;
            $brandNames = config('brand.names', []);
          @endphp
          <div class="po-filter-bar d-flex align-items-center gap-2 justify-content-end">
            @if (auth()->user()->role === 'admin')
              <button class="btn btn-primary btn-sm btnAddPlate" type="button">
                <i class="bx bx-plus me-1"></i> เพิ่มป้ายแดง
              </button>
            @endif
            @if ($canLoan)
              <button class="btn btn-danger btn-sm btnBorrowPlate" type="button">
                <i class="bx bx-transfer me-1"></i> ยืมป้ายแดง
              </button>
            @endif
            <div class="dropdown">
              <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-file me-1"></i> รายงาน
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" data-type="stock" href="{{ route('license.stock-export') }}">
                    ทั้งหมด
                  </a>
                </li>
                <li>
                  <a class="dropdown-item btnExportLicenseAll" data-type="all" href="#">
                    ประวัติการใช้
                  </a>
                </li>
                @if ($canLoan)
                  <li>
                    <a class="dropdown-item" href="{{ route('license.loan-export') }}">
                      ประวัติการยืม
                    </a>
                  </li>
                @endif
              </ul>
            </div>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled licenseTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>เลขป้ายแดง</th>
                  <th>เจ้าของป้าย</th>
                  <th>สถานะ</th>
                  <th>ลูกค้า</th>
                  <th>ฝ่ายขาย</th>
                  <th>วันที่ส่งมอบจริง</th>
                  <th class="tbl-th-action" style="width:150px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- ── Modal เพิ่มป้ายแดง (admin เท่านั้น) ── --}}
  @if (auth()->user()->role === 'admin')
    <div class="modal fade addPlateModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content border-0 shadow mf-content mf-content--input">

          <div class="modal-header mf-header mf-header--input px-4">
            <div class="d-flex align-items-center gap-3">
              <div class="mf-hd-icon">
                <i class="bx bx-plus fs-5 text-white"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มป้ายแดง</h6>
                <small class="text-white mf-hd-sub">Add License Plate</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body mf-body">

            <div class="mf-section">
              <div class="mf-section-hd">
                <div class="mf-section-icon rose">
                  <i class="bx bx-id-card"></i>
                </div>
                <span class="mf-section-title">ข้อมูลป้ายแดง</span>
              </div>
              <div class="mf-section-body">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label for="addPlateNumber" class="mf-label form-label">
                      <i class="bx bx-hash"></i> เลขป้ายแดง <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="addPlateNumber" maxlength="50"
                      placeholder="เช่น ก 2250">
                  </div>

                  <div class="col-md-6">
                    <label for="addPlateBrand" class="mf-label form-label">
                      <i class="bx bx-store"></i> แบรนด์เจ้าของ <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="addPlateBrand">
                      <option value="">— เลือก —</option>
                      @foreach ($brandNames as $bId => $bName)
                        <option value="{{ $bId }}">{{ $bName }}</option>
                      @endforeach
                    </select>
                  </div>

                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ยกเลิก
              </button>
              <button type="button" class="btn btn-primary btnSaveAddPlate">
                <i class="bx bx-save me-1"></i> บันทึก
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Modal ยืมป้ายแดง ── --}}
  @if ($canLoan)
    <div class="modal fade borrowPlateModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
      data-user-brand="{{ $userBrand }}">
      <div class="modal-dialog modal-md" role="document">
        <div class="modal-content border-0 shadow mf-content mf-content--input">

          <div class="modal-header mf-header mf-header--input px-4">
            <div class="d-flex align-items-center gap-3">
              <div class="mf-hd-icon">
                <i class="bx bx-transfer fs-5 text-white"></i>
              </div>
              <div>
                <h6 class="mb-0 fw-bold text-white mf-hd-title">ยืมป้ายแดงข้ามแบรนด์</h6>
                <small class="text-white mf-hd-sub">Borrow License Plate</small>
              </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body mf-body">

            <div class="mf-section">
              <div class="mf-section-hd">
                <div class="mf-section-icon rose">
                  <i class="bx bx-id-card"></i>
                </div>
                <span class="mf-section-title">ข้อมูลการยืม</span>
              </div>
              <div class="mf-section-body">
                <div class="row g-3">

                  {{-- admin (ไม่มีแบรนด์ประจำ) ต้องเลือกแบรนด์ที่ยืมเอง --}}
                  @if (!$userBrand)
                    <div class="col-md-6">
                      <label for="borrowerBrand" class="mf-label form-label">
                        <i class="bx bx-buildings"></i> ยืมให้แบรนด์ <span class="text-danger">*</span>
                      </label>
                      <select class="form-select" id="borrowerBrand">
                        <option value="">— เลือก —</option>
                        @foreach ($brandNames as $bId => $bName)
                          <option value="{{ $bId }}">{{ $bName }}</option>
                        @endforeach
                      </select>
                    </div>
                  @endif

                  <div class="col-md-{{ $userBrand ? 12 : 6 }}">
                    <label for="ownerBrand" class="mf-label form-label">
                      <i class="bx bx-store"></i> ยืมจากแบรนด์ <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="ownerBrand">
                      <option value="">— เลือก —</option>
                      @foreach ($brandNames as $bId => $bName)
                        @if ($bId != $userBrand)
                          <option value="{{ $bId }}">{{ $bName }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label for="borrowPlateId" class="mf-label form-label">
                      <i class="bx bx-id-card"></i> ป้ายแดง <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="borrowPlateId" disabled>
                      <option value="">— เลือกแบรนด์ก่อน —</option>
                    </select>
                    <div class="text-muted small mt-1">เฉพาะป้ายว่างที่ไม่ติดยืม</div>
                  </div>

                  <div class="col-md-6">
                    <label for="borrowDate" class="mf-label form-label">
                      <i class="bx bx-calendar"></i> วันที่ยืม <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="form-control" id="borrowDate" value="{{ now()->format('Y-m-d') }}">
                  </div>

                  <div class="col-md-12">
                    <label for="borrowNote" class="mf-label form-label">
                      <i class="bx bx-note"></i> หมายเหตุ
                    </label>
                    <input type="text" class="form-control" id="borrowNote" maxlength="255"
                      placeholder="ระบุเพิ่มเติม (ถ้ามี)">
                  </div>

                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ยกเลิก
              </button>
              <button type="button" class="btn btn-primary btnSaveBorrow">
                <i class="bx bx-save me-1"></i> บันทึกการยืม
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>
  @endif
@endsection
