@extends('layouts/contentNavbarLayout')
@section('title', 'ข้อมูลฟิล์ม')

@section('page-script')
@vite(['resources/assets/js/stock-film.js'])
@endsection

@section('content')
<div class="inputFilmModal"></div>
<div class="editFilmModal"></div>
<div class="viewMoreFilmModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-layer fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลฟิล์ม</div>
          <div class="text-white mf-hd-sub">Stock Film</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="po-filter-bar d-flex align-items-center justify-content-end gap-2">
          <button type="button" id="btnFilmStockReport" class="btn btn-success btn-sm">
            <i class="bx bx-spreadsheet me-1"></i> รายงานดึง Stock ฟิล์ม
          </button>
          <button class="btn btn-secondary btn-sm btnInputFilm">
            <i class="bx bx-plus me-1"></i> เพิ่มสต็อก
          </button>
        </div>

        <div class="table-responsive mt-2">
          <table class="table table-bordered tbl-table tbl-styled filmStockTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>Stock No.</th>
                <th>Part No.</th>
                {{-- <th>กลุ่มแบรนด์</th> --}}
                <th>ยี่ห้อฟิล์ม</th>
                <th>ความเข้ม</th>
                {{-- <th>วันที่เบิก</th>
                <th class="text-end">จำนวนเริ่มต้น<br><small class="text-muted">(ตร.ฟุต)</small></th>
                <th class="text-end">ใช้ไปแล้ว<br><small class="text-muted">(ตร.ฟุต)</small></th> --}}
                <th class="text-end">คงเหลือ<br><small class="text-muted">(ตร.ฟุต)</small></th>
                <th class="text-center">สถานะ</th>
                {{-- <th>วันที่ตรวจสอบ</th> --}}
                {{-- <th class="text-end">ตรวจสอบคงเหลือ<br><small class="text-muted">(ตร.ฟุต)</small></th> --}}
                {{-- <th class="text-end">ยอดส่วนต่าง<br><small class="text-muted">(ตร.ฟุต)</small></th> --}}
                {{-- <th class="text-center">ผลการตรวจนับ</th> --}}
                <th class="tbl-th-action">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
