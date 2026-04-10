<div class="modal fade editVehicle" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editVehicleLabel">แก้ไขข้อมูลป้ายทะเบียน</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('vehicle.update', $veh->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">

            {{-- @if ($veh->payment_mode == 'non-finance') --}}
            <div class="col-md-4 mb-5">
              <label for="customer_fullname" class="form-label">ลูกค้า</label>
              <input id="customer_fullname" type="text" class="form-control"
                value="{{ $veh->customer?->prefix?->Name_TH ?? '' }} {{ $veh->customer?->FirstName ?? '' }} {{ $lic->saleCarLic?->customer?->LastName ?? '' }}"
                disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="vin_number" class="form-label">Vin-Number</label>
              <input id="vin_number" type="text" class="form-control"
                value="{{ $veh->carOrder?->vin_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="engine_number" class="form-label">เลขถัง</label>
              <input id="engine_number" type="text" class="form-control"
                value="{{ $veh->carOrder?->engine_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-5">
              <label for="withdrawal_total" class="form-label">ยอดตั้งเบิก</label>
              <input id="withdrawal_total" name="withdrawal_total" type="text"
                class="form-control text-end money-input"
                value="{{ number_format($veh->vehicleLicense?->withdrawal_total ?? 0, 2) }}" />
            </div>

            <div class="col-md-2 mb-5">
              <label for="receipt_total" class="form-label">ยอดเคลียร์</label>
              <input id="receipt_total" name="receipt_total" type="text" class="form-control text-end money-input"
                value="{{ number_format($veh->vehicleLicense?->receipt_total ?? 0, 2) }}" />
            </div>

            <div class="col-md-2 mb-5">
              <label for="red_license" class="form-label">เลขป้ายแดง</label>
              <input id="red_license" type="text" class="form-control"
                value="{{ $veh->licensePlateRed?->number ?? '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-5">
              <label for="withdrawal_date" class="form-label">วันที่ตั้งเบิก</label>
              <input id="withdrawal_date" name="withdrawal_date" type="date" class="form-control"
                value="{{ $veh->vehicleLicense?->withdrawal_date ?? '' }}" />
            </div>

            <div class="col-md-3 mb-5">
              <label for="backup_clear_date" class="form-label">วันที่รับป้ายจากขนส่ง</label>
              <input id="backup_clear_date" name="backup_clear_date" type="date" class="form-control"
                value="{{ $veh->vehicleLicense?->backup_clear_date ?? '-' }}" />
            </div>

            {{-- <div class="col-md-3 mb-5">
              <label for="labe_status" class="form-label">ประเภท</label>
              <select id="labe_status" name="labe_status" class="form-select">
                <option value="">- เลือก -</option>
                <option value="postage" {{ $veh->vehicleLicense?->labe_status == 'postage' ? 'selected' : '' }}>
                  ส่งไปรษณีย์</option>
                <option value="customer" {{ $veh->vehicleLicense?->labe_status == 'customer' ? 'selected' : '' }}>
                  ลูกค้ารับเอง</option>
              </select>
            </div> --}}

            <div class="col-md-3 mb-5">
              <label for="license_name" class="form-label">ตัวอักษร</label>
              <input id="license_name" name="license_name" type="text" class="form-control"
                value="{{ $veh->vehicleLicense?->license_name ?? '' }}"
                oninput="this.value = this.value.replace(/[^a-zA-Zก-ฮ]/g, '')">
            </div>

            <div class="col-md-3 mb-5">
              <label for="license_number" class="form-label">ตัวเลข</label>
              <input id="license_number" name="license_number" type="text" class="form-control"
                value="{{ $veh->vehicleLicense?->license_number ?? '' }}"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>

            @php
              $selectedProvince = $veh->vehicleLicense?->license_province ?? $veh->provinces?->id;
            @endphp
            <div class="col-md-6 mb-5">
              <label for="license_province" class="form-label">จังหวัด</label>
              <select id="license_province" name="license_province" class="form-select">
                <option value="">-- เลือกจังหวัด --</option>
                @foreach ($provincesV as $p)
                  <option value="{{ $p->id }}" {{ $selectedProvince == $p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateVehicle">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
