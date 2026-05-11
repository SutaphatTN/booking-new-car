@extends('layouts/contentNavbarLayout')
@section('title', 'Data Sub Model Car')

@section('page-script')
<script>
  window.routeSubModelCreate = "{{ route('model.sub-model.create') }}";
  window.routeSubModelEdit = "{{ route('model.sub-model.edit', ['sub_model_car' => ':id']) }}";
</script>
@vite(['resources/assets/js/car.js'])
@endsection

@section('content')
<div class="viewMoreSubCarModal"></div>
<div class="inputSubCarModal"></div>
<div class="editSubCarModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-git-branch fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลรุ่นรถย่อย</div>
          <div class="text-white mf-hd-sub">Sub Model Car</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputSubCar">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled subCarTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>รายละเอียด</th>
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
@endsection
