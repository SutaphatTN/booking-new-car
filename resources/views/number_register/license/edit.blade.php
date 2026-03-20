<div class="modal fade editLicense" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editLicenseLabel">แก้ไขข้อมูลป้ายแดง</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('vehicle.license.update', $lic->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-3 mb-5">
              <label for="licenseID" class="form-label">เลขป้ายแดง</label>
              <input id="licenseID" type="text" class="form-control" name="licenseID"
                value="{{ $lic->licenseLic?->number ?? '' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label for="license_full" class="form-label">เลขป้ายขาว</label>
              <input id="license_full" type="text" class="form-control"
                value="{{ $lic->saleCarLic?->vehicleLicense?->license_name ?? '' }} {{ $lic->saleCarLic?->vehicleLicense?->license_number ?? '' }}"
                disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label for="delivery_date" class="form-label">วันที่ส่งมอบ</label>
              <input id="delivery_date" type="text" class="form-control"
                value="{{ $lic->saleCarLic?->format_delivery_date ?? '-' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label for="clear_date" class="form-label">วันที่รับป้ายขาว</label>
              <input id="clear_date" type="text" class="form-control"
                value="{{ $lic->saleCarLic?->vehicleLicense?->format_backup_clear_date ?? '-' }}" disabled>
            </div>

            <div class="col-md-6 mb-5">
              <label for="customer_fullname" class="form-label">ลูกค้า</label>
              <input id="customer_fullname" type="text" class="form-control"
                value="{{ $lic->saleCarLic?->customer?->prefix?->Name_TH ?? '' }} {{ $lic->saleCarLic?->customer?->FirstName ?? '' }} {{ $lic->saleCarLic?->customer?->LastName ?? '' }}"
                disabled />
            </div>

            <div class="col-md-6 mb-5">
              <label for="sale_fullname" class="form-label">ฝ่ายขาย</label>
              <input id="sale_fullname" type="text" class="form-control"
                value="{{ $lic->saleCarLic?->saleUser?->name ?? '-' }}" disabled>
            </div>

            <div class="col-md-12 mb-5">
              <label class="form-label">เอกสารป้ายแดง</label>

              <div class="row g-2">
                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;">
                    <input type="checkbox" name="license_red_front" value="1"
                      {{ $lic->license_red_front ? 'checked' : '' }}>
                    <span>ป้ายแดงหน้า</span>
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;">
                    <input type="checkbox" name="license_red_back" value="1"
                      {{ $lic->license_red_back ? 'checked' : '' }}>
                    <span>ป้ายแดงหลัง</span>
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;">
                    <input type="checkbox" name="license_red_book" value="1"
                      {{ $lic->license_red_book ? 'checked' : '' }}>
                    <span>สมุดป้ายแดง</span>
                  </label>
                </div>
              </div>
            </div>

            <div class="col-md-3 mb-5">
              <label for="cust_refund_date" class="form-label">วันที่คืนเงินลูกค้า</label>
              <input id="cust_refund_date" name="cust_refund_date" type="date" class="form-control"
                value="{{ $lic->cust_refund_date ?? '-' }}">
            </div>

            <div class="col-md-2 mb-5">
              <label for="refund_amount" class="form-label">ยอดคืนเงิน</label>
              <input id="refund_amount" name="refund_amount" type="text" class="form-control text-end money-input"
                value="{{ number_format($lic->refund_amount ?? 3000, 2) }}">
            </div>

            <div class="col-md-2 mb-5">
              <label for="type_refund" class="form-label">ประเภท</label>
              <select id="type_refund" name="type_refund" class="form-select">
                <option value="">- เลือก -</option>
                <option value="cash" {{ $lic->type_refund == 'cash' ? 'selected' : '' }}>เงินสด</option>
                <option value="transfer" {{ $lic->type_refund == 'transfer' ? 'selected' : '' }}>โอน</option>
              </select>
            </div>

            <div class="col-md-5 mb-5">
              <label for="note" class="form-label">หมายเหตุ</label>
              <textarea id="note" class="form-control" name="note" rows="1">{{ $lic->note }}</textarea>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateLicense">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
