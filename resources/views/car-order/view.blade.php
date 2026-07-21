@extends('layouts/contentNavbarLayout')
@section('title', 'Data Car Order')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="viewMoreCarOrder"></div>
<div class="editCarOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-car fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลรายการรถ</div>
          <div class="text-white mf-hd-sub">Car Order</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar : ปุ่มรายงาน (ซ้าย) | ฟิลเตอร์รุ่นหลัก-ย่อย (ขวา) ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between gap-3 flex-wrap">

          <button type="button" class="btn btn-success btn-sm btnCarOrderReport">
            <i class="bx bx-download me-1"></i> รายงานข้อมูลรถ
          </button>

          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label class="mb-0">รุ่นรถหลัก :</label>
              <select id="filter_model" class="form-select form-select-sm" style="width:160px;">
                <option value="">-- ทั้งหมด --</option>
                @foreach($model as $model)
                <option value="{{ $model->id }}">{{ $model->Name_TH }}</option>
                @endforeach
              </select>
            </div>
            <div class="d-flex align-items-center gap-2">
              <label class="mb-0">รุ่นรถย่อย :</label>
              <select id="filter_subModel" class="form-select form-select-sm" style="width:200px;" disabled>
                <option value="">-- ทั้งหมด --</option>
              </select>
            </div>
          </div>

        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled carOrderTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถ</th>
                <th>Vin Number</th>
                <th>J Number</th>
                <th>วันที่</th>
                <th>สถานะ</th>
                <th class="tbl-th-action" style="width:150px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<div id="carOrderLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
