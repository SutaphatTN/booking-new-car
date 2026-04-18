@extends('layouts/contentNavbarLayout')
@section('title', 'รายการถอนจอง')

@section('page-script')
  @vite(['resources/assets/js/cancellation.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <h4 class="card-header">รายการถอนจอง</h4>
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
      <div class="modal-content border-0 shadow mf-content mf-content--view">

        <div class="modal-header mf-header mf-header--view px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-info-circle fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลถอนจอง</h6>
              <small class="text-white mf-hd-sub">Cancellation Detail</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body mf-body">

          {{-- Section 1 : ข้อมูลลูกค้าและรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-user"></i>
              </div>
              <span class="mf-section-title">ข้อมูลลูกค้าและรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <div class="mf-label">
                    <i class="bx bx-user-circle"></i> ชื่อ - นามสกุล
                  </div>
                  <div class="mf-val" id="viewFullName"></div>
                </div>

                <div class="col-12">
                  <div class="mf-label">
                    <i class="bx bx-car"></i> รุ่นรถ
                  </div>
                  <div class="mf-val" id="viewModel"></div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลการถอนจอง --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-calendar-x"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการถอนจอง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <div class="mf-label">
                    <i class="bx bx-calendar-minus ci-rose"></i> วันที่ถอนจอง
                  </div>
                  <div class="mf-val" id="viewCancelDate"></div>
                </div>

                <div class="col-md-4">
                  <div class="mf-label">
                    <i class="bx bx-calendar-check ci-rose"></i> วันที่คืนเงิน
                  </div>
                  <div class="mf-val" id="viewRefundDate"></div>
                </div>

                <div class="col-md-4">
                  <div class="mf-label">
                    <i class="bx bx-buildings ci-rose"></i> วันที่ Motor คืนเงิน
                  </div>
                  <div class="mf-val" id="viewRefundMotorDate"></div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : หลักฐาน (แสดงเฉพาะเมื่อมีข้อมูล) --}}
          <div class="mf-section" id="viewWithdrawAttachSection" style="display:none;">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-paperclip"></i>
              </div>
              <span class="mf-section-title">หลักฐานการคืนเงินถอนจอง</span>
            </div>
            <div class="mf-section-body">
              <div id="viewWithdrawAttachList" class="row g-2"></div>
            </div>
          </div>

          {{-- <div class="d-flex justify-content-end pt-1">
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ปิด
            </button>
          </div> --}}

        </div>
      </div>
    </div>
  </div>

  {{-- Edit Modal --}}
  <div class="modal fade" id="cancellationEditModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">

        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-edit-alt fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลถอนจอง</h6>
              <small class="text-white mf-hd-sub">Edit Cancellation</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body mf-body">

          {{-- Section 1 : วันที่สำคัญ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-calendar"></i>
              </div>
              <span class="mf-section-title">วันที่สำคัญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="editCancelDate" class="mf-label form-label">
                    <i class="bx bx-calendar-minus"></i> วันที่ถอนจอง
                  </label>
                  <input type="date" id="editCancelDate" class="form-control">
                </div>

                <div class="col-md-4">
                  <label for="editRefundDate" class="mf-label form-label">
                    <i class="bx bx-calendar-check"></i> วันที่คืนเงิน
                  </label>
                  <input type="date" id="editRefundDate" class="form-control">
                </div>

                <div class="col-md-4">
                  <label for="editRefundMotorDate" class="mf-label form-label">
                    <i class="bx bx-buildings"></i> วันที่ Motor คืนเงิน
                  </label>
                  <input type="date" id="editRefundMotorDate" class="form-control">
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : แนบเอกสาร --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-paperclip"></i>
              </div>
              <span class="mf-section-title">หลักฐานการคืนเงินถอนจอง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="withdrawAttachmentInput" class="mf-label form-label">
                    <i class="bx bx-upload ci-indigo"></i> แนบเอกสาร
                    <span class="mf-label-note">รองรับ PDF, JPG, PNG (เลือกได้หลายไฟล์)</span>
                  </label>
                  <input type="file" id="withdrawAttachmentInput" class="form-control"
                    accept=".pdf,.jpg,.jpeg,.png" multiple>
                </div>

                <div class="col-12">
                  <div id="withdrawAttachmentList" class="row g-2 mb-3"></div>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5" id="btnSaveEdit">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection
