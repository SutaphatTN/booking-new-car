@extends('layouts/contentNavbarLayout')
@section('title', 'นำเข้า WS')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-import fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">นำเข้า WS</div>
          <div class="text-white mf-hd-sub">Import WS to Car Order</div>
        </div>
      </div>

      <div class="card-body pt-4">

        {{-- ── วิธีใช้งาน ── --}}
        <div class="alert alert-info">
          <div class="fw-bold mb-1"><i class="bx bx-info-circle me-1"></i> วิธีใช้งาน</div>
          <ol class="mb-0 ps-3">
            <li>กดปุ่ม <b>ดาวน์โหลดไฟล์ต้นฉบับ</b> เพื่อรับไฟล์ <code>.xlsx</code> ที่มีหัวคอลัมน์
              <code>vin_number</code> และ <code>WS</code></li>
            <li>กรอกเลขตัวถัง (VIN) และค่า WS ที่ต้องการ แล้วบันทึกไฟล์</li>
            <li>อัปโหลดไฟล์กลับเข้ามา ระบบจะค้นหาตามเลข VIN แล้วอัปเดตค่า WS ในใบสั่งซื้อ</li>
          </ol>
          <div class="mt-2 small">
            <i class="bx bx-coin me-1"></i>
            <b>ค่า WS:</b> แนะนำกรอกเป็นตัวเลขเฉยๆ เช่น <code>1500</code>
            (จะใส่ comma เช่น <code>1,500</code> หรือทศนิยมสูงสุด 2 ตำแหน่ง เช่น <code>1500.50</code> ก็ได้)
          </div>
        </div>

        {{-- ── ปุ่มดาวน์โหลด + ฟอร์มอัปโหลด ── --}}
        <div class="d-flex flex-wrap gap-3 align-items-end">
          <a href="{{ route('car-order.import-ws.template') }}" class="btn btn-outline-secondary">
            <i class="bx bx-download me-1"></i> ดาวน์โหลดไฟล์ต้นฉบับ
          </a>

          <form id="formImportWs" class="d-flex flex-wrap gap-2 align-items-end" enctype="multipart/form-data">
            @csrf
            <div>
              <label for="wsFile" class="form-label mb-1">เลือกไฟล์ (.xlsx)</label>
              <input type="file" id="wsFile" name="file" accept=".xlsx" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-upload me-1"></i> นำเข้าข้อมูล
            </button>
          </form>
        </div>

        {{-- ── ผลการนำเข้า ── --}}
        <div id="importResult" class="mt-4 d-none">
          <hr>
          <h6 class="fw-bold">ผลการนำเข้า</h6>
          <ul class="mb-2">
            <li>อัปเดตสำเร็จ: <span id="resUpdated" class="fw-bold text-success">0</span> รายการ</li>
            <li>ข้าม (ไม่ได้กรอกค่า WS): <span id="resSkipped" class="fw-bold text-muted">0</span> รายการ</li>
            <li>ไม่พบเลข VIN: <span id="resNotFound" class="fw-bold text-danger">0</span> รายการ</li>
          </ul>
          <div id="notFoundList" class="small text-danger"></div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  $(function () {
    $('#formImportWs').on('submit', function (e) {
      e.preventDefault();

      const fileInput = $('#wsFile')[0];
      if (!fileInput.files.length) {
        Swal.fire({ icon: 'warning', title: 'กรุณาเลือกไฟล์' });
        return;
      }

      const formData = new FormData(this);

      Swal.fire({
        title: 'กำลังนำเข้าข้อมูล...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      $.ajax({
        url: '{{ route('car-order.import-ws.store') }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
          Swal.close();

          if (!res.success) {
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: res.message || 'นำเข้าไม่สำเร็จ' });
            return;
          }

          $('#resUpdated').text(res.updated);
          $('#resSkipped').text(res.skipped);
          $('#resNotFound').text(res.notFound.length);

          if (res.notFound.length) {
            $('#notFoundList').html('<b>VIN ที่ไม่พบ:</b> ' + res.notFound.join(', '));
          } else {
            $('#notFoundList').html('');
          }

          $('#importResult').removeClass('d-none');
          $('#formImportWs')[0].reset();

          Swal.fire({
            icon: 'success',
            title: 'นำเข้าสำเร็จ',
            text: 'อัปเดต WS ' + res.updated + ' รายการ'
          });
        },
        error: function (xhr) {
          Swal.close();
          let msg = 'เกิดข้อผิดพลาด';
          if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
          Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
        }
      });
    });
  });
</script>
@endsection
