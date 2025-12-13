<div class="modal fade viewAcc" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewAccLabel">ข้อมูลประดับยนต์</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input id="model_id" class="form-control" type="text" value="{{ $acc->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
            <input id="subModel_id" class="form-control" type="text" value="{{ $acc->subModel->name }}" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label for="accessory_id" class="form-label">รหัสเครื่องประดับ</label>
            <input id="accessory_id" class="form-control" type="text" value="{{ $acc->accessory_id }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="accessoryPartner_id" class="form-label">แหล่งที่มา</label>
            <input id="accessoryPartner_id" class="form-control" type="text" value="{{ $acc->partner->name }}" disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="accessoryType_id" class="form-label">ประเภท</label>
            <input id="accessoryType_id" class="form-control" type="text" value="{{ $acc->type->name }}" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label for="detail" class="form-label">รายละเอียด</label>
            <textarea id="detail" name="detail" class="form-control" disabled>{{ $acc->detail ?: '-' }}</textarea>
          </div>

          <div class="col-md-6 mb-5">
            <label for="cost" class="form-label">ราคาทุน</label>
            <input id="cost" class="form-control text-end" type="text"
              value="{{ $acc->cost !== null ? number_format($acc->cost, 2) : '-' }}"
              disabled />
          </div>
          <div class="col-md-6 mb-5">
            <label for="promo" class="form-label">ราคาพิเศษ</label>
            <input id="promo" class="form-control text-end" type="text"
              value="{{ $acc->promo !== null ? number_format($acc->promo, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="sale" class="form-label">ราคาขาย</label>
            <input id="sale" class="form-control text-end" type="text"
              value="{{ $acc->sale !== null ? number_format($acc->sale, 2) : '-' }}"
              disabled />
          </div>
          <div class="col-md-6 mb-5">
            <label for="comSale" class="form-label">ค่าคอม ราคาขาย</label>
            <input id="comSale" class="form-control text-end" type="text"
              value="{{ $acc->comSale !== null ? number_format($acc->comSale, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-6 mb-5">
            <label for="startDate" class="form-label">วันที่เริ่ม</label>
            <input id="startDate" class="form-control" type="text" value="{{ $acc->format_start_date }}" disabled />
          </div>
          <div class="col-md-6 mb-5">
            <label for="endDate" class="form-label">วันที่สิ้นสุด</label>
            <input id="endDate" class="form-control" type="text" value="{{ $acc->format_end_date }}" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>