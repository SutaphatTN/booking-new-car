@extends('layouts/contentNavbarLayout')
@section('title', 'ค้างคืนเงินป้ายแดง')

@section('page-script')
  @vite(['resources/assets/js/license.js'])
@endsection

@section('content')
  {{-- โมดัลของหน้าป้ายแดงใช้ร่วมกัน (ดูข้อมูล/แก้ไข) — ตัวจัดการอยู่ใน license.js ตัวเดียวกัน --}}
  <div class="viewMoreLicenseModel"></div>
  <div class="editLicenseModel"></div>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-time-five fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ค้างคืนเงินป้ายแดง</div>
            <div class="text-white mf-hd-sub">Red Plate — Pending Refund</div>
          </div>
        </div>

        <div class="card-body pt-3">
          <div class="text-muted small mb-3">
            <i class="bx bx-info-circle"></i>
            รายการที่ <b>คืนป้ายไปก่อนแล้ว</b> (ป้ายกลับเข้าสต็อก/ผูกกับลูกค้ารายใหม่ได้) แต่ <b>ยังไม่ได้ปิดเรื่องเงิน</b>
            &nbsp;•&nbsp; กรอกข้อมูลให้ครบแล้วกดปุ่มเขียวเพื่อปิดรายการ — ใช้เงื่อนไขเดียวกับปุ่ม "ยืนยันการจ่ายเงินจริง" ในหน้าป้ายแดง
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled pendingRefundTable w-100">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ลูกค้า</th>
                  <th style="width:110px;">ป้ายแดง</th>
                  <th>เลขตัวถัง</th>
                  <th>ฝ่ายขาย</th>
                  <th style="width:110px;">วันที่คืนป้าย</th>
                  <th>ผู้คืนป้าย</th>
                  <th>เหตุผล</th>
                  <th style="width:130px;">สถานะข้อมูล</th>
                  <th class="tbl-th-action" style="width:140px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
@endsection
