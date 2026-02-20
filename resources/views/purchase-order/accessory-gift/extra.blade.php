<div class="modal fade viewExtra" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h5 class="modal-title">เพิ่มรายการ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-12">
            <fieldset class="mb-0">
              <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ค้นหาชื่อหรือรหัส</legend>
              <div class="input-group">
                <input id="extraSearch" type="text" class="form-control" placeholder="พิมพ์รหัสหรือชื่อ...">
                <input type="hidden" id="subModel_id_extra" value="{{ $saleCar->subModel_id }}">
                <span class="btn btn-outline-secondary btnExtraSearch" style="cursor:pointer;">
                  <i class="bx bx-search"></i>
                </span>
              </div>
            </fieldset>
          </div>
        </div>

        <div class="table-responsive table-scroll">
          <table class="table table-bordered" id="tableExtraResult">
            <thead>
              <tr>
                <th class="text-center">รหัส</th>
                <th>รายละเอียด</th>
                <th class="text-center">ราคาทุน</th>
                <th class="text-center">ราคาพิเศษ</th>
                <th class="text-center">ราคาขาย (ค่าคอม)</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <div class="d-flex justify-content-end pb-3 pe-3">
        <button type="button" id="btnSaveExtra" class="btn btn-primary">บันทึก</button>
      </div>
    </div>
  </div>
</div>

<style>
  .table-scroll {
    max-height: 350px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
  }

  .table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #fff;
  }

  .table-scroll tfoot td {
    position: sticky;
    bottom: 0;
    background: #fff;
    z-index: 4;
    box-shadow: 0 -2px 3px rgba(0, 0, 0, 0.05);
  }
</style>