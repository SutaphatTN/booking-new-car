<div class="modal fade viewVehicle" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewVehicleLabel">ข้อมูลป้ายทะเบียน</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">

          <div class="col-md-4 mb-5">
            <label for="customer_fullname" class="form-label">ลูกค้า</label>
            <input id="customer_fullname" type="text" class="form-control"
              value="{{ $veh->customer?->prefix?->Name_TH ?? '' }} {{ $veh->customer?->FirstName ?? '' }} {{ $lic->saleCarLic?->customer?->LastName ?? '' }}"
              disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="vin_number" class="form-label">Vin-Number</label>
            <input id="vin_number" type="text" class="form-control" value="{{ $veh->carOrder?->vin_number ?? '-' }}"
              disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="engine_number" class="form-label">เลขถัง</label>
            <input id="engine_number" type="text" class="form-control" value="{{ $veh->carOrder?->engine_number ?? '-' }}"
              disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="withdrawal_total" class="form-label">ยอดตั้งเบิก</label>
            <input id="withdrawal_total" name="withdrawal_total" type="text"
              class="form-control text-end money-input"
              value="{{ number_format($veh->vehicleLicense?->withdrawal_total ?? 0, 2) }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="receipt_total" class="form-label">ยอดเคลียร์</label>
            <input id="receipt_total" name="receipt_total" type="text" class="form-control text-end money-input"
              value="{{ number_format($veh->vehicleLicense?->receipt_total ?? 0, 2) }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="red_license" class="form-label">เลขป้ายแดง</label>
            <input id="red_license" type="text" class="form-control"
              value="{{ $veh->licensePlateRed?->number ?? '-' }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="withdrawal_date" class="form-label">วันที่ตั้งเบิก</label>
            <input id="withdrawal_date" name="withdrawal_date" type="text" class="form-control"
              value="{{ $veh->vehicleLicense?->format_withdrawal_date ?? '' }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="backup_clear_date" class="form-label">วันที่รับป้ายจากขนส่ง</label>
            <input id="backup_clear_date" name="backup_clear_date" type="text" class="form-control"
              value="{{ $veh->vehicleLicense?->format_backup_clear_date ?? '-' }}" disabled />
          </div>

          @php
            $statusMap = [
                'postage' => 'ส่งไปรษณีย์',
                'customer' => 'ลูกค้ารับเอง',
            ];
          @endphp
          {{-- <div class="col-md-3 mb-5">
            <label for="labe_status" class="form-label">ประเภท</label>
            <input id="labe_status" type="text" class="form-control"
              value="{{ $statusMap[$veh->vehicleLicense?->labe_status] ?? '-' }}" disabled />
          </div> --}}

          <div class="col-md-3 mb-5">
            <label for="license_name" class="form-label">ตัวอักษร</label>
            <input id="license_name" name="license_name" type="text" class="form-control"
              value="{{ $veh->vehicleLicense?->license_name ?? '' }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="license_number" class="form-label">ตัวเลข</label>
            <input id="license_number" name="license_number" type="text" class="form-control"
              value="{{ $veh->vehicleLicense?->license_number ?? '' }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="license_province" class="form-label">จังหวัด</label>
            <input id="license_province" name="license_province" type="text" class="form-control"
              value="{{ $veh->vehicleLicense?->provincesV?->name ?? '' }}" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
