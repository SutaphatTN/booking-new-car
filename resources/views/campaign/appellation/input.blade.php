<div class="modal fade inputCamAppellation" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputCamAppellationLabel">เพิ่มชื่อแคมเปญ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('campaign.storeAppellation') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-12 mb-5">
              <label for="name" class="form-label">ชื่อแคมเปญ</label>
              <input id="name" type="text"
                class="form-control @error('name') is-invalid @enderror"
                name="name" autocomplete="off" required>

              @error('name')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreCampaignAppellation">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>