{{-- modal กรอกข้อมูลลูกค้าที่ยังขาด (เลขบัตร/เบอร์โทร/ที่อยู่) ก่อนทำการจอง
     ใช้ร่วมกันระหว่างหน้าเพิ่มการจอง กับหน้าคำขออนุมัติเกินงบ (ตอนกดสร้างการจอง) — JS: customer-complete.js --}}
@php $prefixes ??= \App\Models\TbPrefixname::all(); @endphp
<div class="modal fade" id="modalCompleteCustomerPO" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-id-card fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">กรอกข้อมูลลูกค้าให้ครบก่อนทำการจอง</h6>
            <small class="text-white mf-hd-sub">Complete Customer Information</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3" role="alert">
          <i class="bx bx-info-circle fs-5"></i>
          <div>ลูกค้ารายนี้ยังมีข้อมูลไม่ครบ — <span id="ccpo_missing"></span></div>
        </div>

        {{-- ชื่อ - นามสกุล --}}
        <div class="row g-3 mb-2">
          <div class="col-md-3">
            <label class="po-label" for="ccpo_prefix"><i class='bx bx-list-ul'></i> คำนำหน้า</label>
            <select id="ccpo_prefix" class="form-select">
              <option value="">— เลือก —</option>
              @foreach ($prefixes as $p)
                <option value="{{ $p->id }}">{{ $p->Name_TH }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="po-label" for="ccpo_first_name"><i class='bx bxs-user'></i> ชื่อ <span
                class="text-danger">*</span></label>
            <input id="ccpo_first_name" type="text" class="form-control">
          </div>
          <div class="col-md-5">
            <label class="po-label" for="ccpo_last_name"><i class='bx bxs-user'></i> นามสกุล</label>
            <input id="ccpo_last_name" type="text" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="po-label" for="ccpo_original_name"><i class='bx bxs-user-rectangle'></i> ชื่อตอนจอง</label>
            <input id="ccpo_original_name" type="text" class="form-control" readonly
              style="background:#f8fafc;">
          </div>
        </div>

        {{-- ข้อมูลส่วนตัว --}}
        <div class="row g-3 mb-2">
          <div class="col-md-6">
            <label class="po-label" for="ccpo_id_number"><i class='bx bx-id-card'></i> เลขบัตรประชาชน <span
                class="text-danger">*</span></label>
            <input id="ccpo_id_number" type="text" class="form-control" maxlength="17"
              placeholder="x-xxxx-xxxxx-xx-x">
          </div>
          <div class="col-md-6">
            <label class="po-label" for="ccpo_phone"><i class='bx bx-phone'></i> เบอร์โทรศัพท์ <span
                class="text-danger">*</span></label>
            <input id="ccpo_phone" type="text" class="form-control" maxlength="12" placeholder="xxx-xxxx-xxx">
          </div>
        </div>

        {{-- ที่อยู่ปัจจุบัน --}}
        <div class="po-label mt-4 mb-1"><i class='bx bx-home-alt'></i> ที่อยู่ปัจจุบัน</div>
        <div class="row g-3">
          <div class="col-md-3">
            <label class="po-label" for="ccpo_house_number">เลขที่ <span class="text-danger">*</span></label>
            <input id="ccpo_house_number" type="text" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="po-label" for="ccpo_group">หมู่ที่</label>
            <input id="ccpo_group" type="text" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="po-label" for="ccpo_alley">ซอย</label>
            <input id="ccpo_alley" type="text" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="po-label" for="ccpo_village">หมู่บ้าน</label>
            <input id="ccpo_village" type="text" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="po-label" for="ccpo_road">ถนน</label>
            <input id="ccpo_road" type="text" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="po-label" for="ccpo_province">จังหวัด <span class="text-danger">*</span></label>
            <select id="ccpo_province" class="form-select">
              <option value="">— เลือกจังหวัด —</option>
            </select>
          </div>
          <div class="col-md-5">
            <label class="po-label" for="ccpo_district">อำเภอ/เขต <span class="text-danger">*</span></label>
            <select id="ccpo_district" class="form-select" disabled>
              <option value="">— เลือกอำเภอ —</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="po-label" for="ccpo_subdistrict">ตำบล/แขวง <span class="text-danger">*</span></label>
            <select id="ccpo_subdistrict" class="form-select" disabled>
              <option value="">— เลือกตำบล —</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="po-label" for="ccpo_postal_code">รหัสไปรษณีย์</label>
            <input id="ccpo_postal_code" type="text" class="form-control" readonly>
          </div>
          <input type="hidden" id="ccpo_post_id">
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="btnSaveCompleteCustomerPO">
            <i class="bx bx-save me-1"></i> บันทึกข้อมูล
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
