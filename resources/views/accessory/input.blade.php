<div class="modal fade inputAcc" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputAccLabel">เพิ่มข้อมูลประดับยนต์</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('accessory.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-6 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="accessory_id" class="form-label">รหัสเครื่องประดับ</label>
              <input id="accessory_id" type="text"
                class="form-control @error('accessory_id') is-invalid @enderror"
                name="accessory_id" required>

              @error('accessory_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="accessoryPartner_id" class="form-label">แหล่งที่มา</label>
              <select id="accessoryPartner_id" name="accessoryPartner_id" class="form-select @error('accessoryPartner_id') is-invalid @enderror" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                @foreach ($partner as $p)
                <option value="{{ @$p->id }}">{{ @$p->name }}</option>
                @endforeach
              </select>

              @error('accessoryPartner_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="accessoryType_id" class="form-label">ประเภท</label>
              <select id="accessoryType_id" name="accessoryType_id" class="form-select @error('accessoryType_id') is-invalid @enderror" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                @foreach ($type as $t)
                <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                @endforeach
              </select>

              @error('accessoryType_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="accessory_id" class="form-label">รายละเอียด</label>
              <textarea id="detail"
                class="form-control @error('detail') is-invalid @enderror"
                name="detail"
                rows="3" required>{{ old('detail') }}</textarea>

              @error('detail')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="cost" class="form-label">ราคาทุน</label>
              <input id="cost" type="text"
                class="form-control text-end money-input @error('cost') is-invalid @enderror"
                name="cost" required>

              @error('cost')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-6 mb-5">
              <label for="promo" class="form-label">ราคาพิเศษ</label>
              <input id="promo" type="text"
                class="form-control text-end money-input"
                name="promo">
            </div>

            <div class="col-md-6 mb-5">
              <label for="sale" class="form-label">ราคาขาย</label>
              <input id="sale" type="text"
                class="form-control text-end money-input @error('sale') is-invalid @enderror"
                name="sale" required>

              @error('sale')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-6 mb-5">
              <label for="comSale" class="form-label">ค่าคอม ราคาขาย</label>
              <input id="comSale" type="text"
                class="form-control text-end money-input"
                name="comSale">
            </div>

            <div class="col-md-6 mb-5">
              <label for="startDate" class="form-label">วันที่เริ่ม</label>
              <input id="startDate" type="date"
                class="form-control @error('startDate') is-invalid @enderror"
                name="startDate" required>

              @error('startDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-6 mb-5">
              <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
              <input id="endDate" type="date"
                class="form-control @error('endDate') is-invalid @enderror"
                name="endDate" required>

              @error('endDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreAccessory">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>