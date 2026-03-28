@extends('layouts/contentNavbarLayout')
@section('title', 'รายการถอนจอง')

@section('page-script')
  @vite(['resources/assets/js/cancellation.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <h4 class="card-header" style="text-align:center;">รายการถอนจอง</h4>
        <div class="card-body">
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered" id="cancellationTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>รุ่นรถ</th>
                  <th>วันที่ถอนจอง</th>
                  <th width="150px">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- View Modal --}}
  <div class="modal fade" id="cancellationViewModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h4 class="modal-title mb-2">ข้อมูลถอนจอง</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 row align-items-center">
            <label class="col-4 fw-bold">ชื่อ - นามสกุล</label>
            <div class="col-8" id="viewFullName"></div>
          </div>
          <div class="mb-3 row align-items-center">
            <label class="col-4 fw-bold">รุ่นรถ</label>
            <div class="col-8" id="viewModel"></div>
          </div>
          <div class="mb-3 row align-items-center">
            <label class="col-4 fw-bold">วันที่ถอนจอง</label>
            <div class="col-8" id="viewCancelDate"></div>
          </div>
          <div class="mb-3 row align-items-center">
            <label class="col-4 fw-bold">วันที่คืนเงิน</label>
            <div class="col-8" id="viewRefundDate"></div>
          </div>
          <div class="mb-3 row align-items-center">
            <label class="col-4 fw-bold">วันที่ Motor คืนเงิน</label>
            <div class="col-8" id="viewRefundMotorDate"></div>
          </div>
          <div id="viewWithdrawAttachSection" style="display:none;">
            <hr>
            <p class="fw-bold mb-2">หลักฐานการคืนเงินถอนจอง</p>
            <div id="viewWithdrawAttachList" class="row g-2"></div>
          </div>
        </div>
        {{-- <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ปิด</button>
          <button type="button" class="btn btn-primary" id="btnSaveRefundDate">บันทึก</button>
        </div> --}}
      </div>
    </div>
  </div>

  {{-- Edit Modal --}}
  <div class="modal fade" id="cancellationEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header border-bottom">
          <h4 class="modal-title mb-2">แก้ไขข้อมูลถอนจอง</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">วันที่ถอนจอง</label>
              <input type="date" id="editCancelDate" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">วันที่คืนเงิน</label>
              <input type="date" id="editRefundDate" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">วันที่ Motor คืนเงิน</label>
              <input type="date" id="editRefundMotorDate" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">แนบเอกสาร</label>
              <input type="file" id="withdrawAttachmentInput" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
              <small class="text-muted">รองรับไฟล์ PDF, JPG, PNG (เลือกได้หลายไฟล์)</small>
            </div>
          </div>

          <hr>

          <div class="mb-3">
            <label class="form-label fw-semibold">หลักฐานการคืนเงินถอนจอง</label>
            <div id="withdrawAttachmentList" class="row g-2 mb-3"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="btnSaveEdit">บันทึก</button>
        </div>
      </div>
    </div>
  </div>
@endsection
