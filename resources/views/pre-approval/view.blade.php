@extends('layouts/contentNavbarLayout')
@section('title', 'ขออนุมัติเกินงบล่วงหน้า')

@section('page-script')
@vite(['resources/assets/js/pre-approval.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-file-find fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ขออนุมัติเกินงบล่วงหน้า</div>
          <div class="text-white mf-hd-sub">Pre-Approval (Over Budget)</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="alert alert-info py-2 mb-3" style="font-size:.85rem;">
          <i class="bx bx-info-circle me-1"></i>
          รายการในหน้านี้ <strong>ยังไม่เป็นการจอง</strong> — รับเฉพาะกรณี <strong>เกินงบทะลุเพดาน</strong>
          เมื่อได้รับอนุมัติแล้วจึงกด <strong>“สร้างการจอง”</strong> เพื่อส่งเข้าระบบจอง
        </div>

        <div class="d-flex justify-content-end mb-3">
          <a href="{{ route('purchase-order.create', ['pre_approval' => 1]) }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>สร้างคำขออนุมัติ
          </a>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled" id="preApprovalTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>รุ่นรถ</th>
                <th>ชื่อฝ่ายขาย</th>
                <th>วันที่ขออนุมัติ</th>
                <th>สถานะ</th>
                <th class="tbl-th-action" style="width:120px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
