<div class="modal fade viewAccessory" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title">เพิ่มของแถม</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-12">
            <label class="form-label">ค้นหาชื่อหรือรหัส</label>
            <div class="input-group">
              <input id="accessorySearch" type="text" class="form-control" placeholder="พิมพ์รหัสหรือชื่อ...">
              <input type="hidden" id="CarModelID" value="{{ $saleCar->CarModelID }}">
              <span class="input-group-text btnAccessorySearch" style="cursor:pointer;">
                <i class="bx bx-search"></i>
              </span>
            </div>
          </div>
        </div>

        <table class="table table-bordered" id="tableAccessoryResult">
          <thead>
            <tr>
              <th class="text-center">รหัส</th>
              <th>รายละเอียด</th>
              <th class="text-center">ราคาทุน</th>
              <th class="text-center">ราคาพิเศษ</th>
              <th class="text-center">ราคาขาย</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="d-flex justify-content-end pb-3 pe-3">
        <button id="btnSaveAccessory" class="btn btn-primary">บันทึก</button>
      </div>
    </div>
  </div>
</div>