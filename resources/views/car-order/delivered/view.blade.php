@extends('layouts/contentNavbarLayout')
@section('title', 'รถที่ส่งมอบ')

@section('page-script')
  @vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
  {{-- โมดัลดูข้อมูล/แก้ไข ใช้ตัวเดียวกับหน้ารายการรถ (handler อยู่ใน car-order.js) --}}
  <div class="viewMoreCarOrder"></div>
  <div class="editCarOrderModal"></div>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-check-circle fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รถที่ส่งมอบ</div>
            <div class="text-white mf-hd-sub">Delivered Cars</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled carOrderDeliveredTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อลูกค้า</th>
                  <th>Vin Number</th>
                  <th class="tbl-th-action" style="width:120px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div id="carOrderDeliveredLoadingOverlay" style="display:flex;">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>
@endsection
