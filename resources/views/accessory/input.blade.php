<div class="modal fade inputAcc" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-package fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลประดับยนต์</h6>
            <small class="text-white mf-hd-sub">Add Accessory</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('accessory.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Section 1 : ข้อมูลทั่วไป --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลทั่วไป</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-6">
                  <label for="inp_acc_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="inp_acc_model_id" name="model_id"
                    class="form-select @error('model_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="inp_acc_accessory_id" class="mf-label form-label">
                    <i class="bx bx-barcode"></i> รหัสเครื่องประดับ <span class="text-danger">*</span>
                  </label>
                  <input id="inp_acc_accessory_id" type="text"
                    class="form-control @error('accessory_id') is-invalid @enderror" name="accessory_id"
                    placeholder="ระบุรหัส..." required>
                  @error('accessory_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="inp_acc_partner" class="mf-label form-label">
                    <i class="bx bx-store"></i> แหล่งที่มา <span class="text-danger">*</span>
                  </label>
                  <select id="inp_acc_partner" name="accessoryPartner_id"
                    class="form-select @error('accessoryPartner_id') is-invalid @enderror" required>
                    <option value="">— เลือกแหล่งที่มา —</option>
                    @foreach ($partner as $p)
                      <option value="{{ @$p->id }}">{{ @$p->name }}</option>
                    @endforeach
                  </select>
                  @error('accessoryPartner_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="inp_acc_type" class="mf-label form-label">
                    <i class="bx bx-list-ul"></i> ประเภท <span class="text-danger">*</span>
                  </label>
                  <select id="inp_acc_type" name="accessoryType_id"
                    class="form-select @error('accessoryType_id') is-invalid @enderror" required>
                    <option value="">— เลือกประเภท —</option>
                    @foreach ($type as $t)
                      <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                    @endforeach
                  </select>
                  @error('accessoryType_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-12">
                  <label for="inp_acc_detail" class="mf-label form-label">
                    <i class="bx bx-align-left"></i> รายละเอียด <span class="text-danger">*</span>
                  </label>
                  <textarea id="inp_acc_detail" class="form-control @error('detail') is-invalid @enderror" name="detail" rows="3"
                    placeholder="ระบุรายละเอียด..." required>{{ old('detail') }}</textarea>
                  @error('detail')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลราคา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลราคา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="inp_acc_cost_spare" class="mf-label form-label">
                    <i class="bx bx-wrench ci-amber"></i> ราคาทุนอะไหล่ <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_acc_cost_spare" type="text"
                      class="form-control text-end money-input @error('cost_spare') is-invalid @enderror"
                      name="cost_spare" placeholder="0.00" required>
                  </div>
                  @error('cost_spare')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="inp_acc_cost" class="mf-label form-label">
                    <i class="bx bx-store ci-amber"></i> ราคาทุน <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_acc_cost" type="text"
                      class="form-control text-end money-input @error('cost') is-invalid @enderror" name="cost"
                      placeholder="0.00" required>
                  </div>
                  @error('cost')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="inp_acc_promo" class="mf-label form-label">
                    <i class="bx bx-gift ci-amber"></i> ราคาพิเศษ
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_acc_promo" type="text" class="form-control text-end money-input"
                      name="promo" placeholder="0.00">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="inp_acc_sale" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_acc_sale" type="text"
                      class="form-control text-end money-input @error('sale') is-invalid @enderror" name="sale"
                      placeholder="0.00" required>
                  </div>
                  @error('sale')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="inp_acc_comSale" class="mf-label form-label">
                    <i class="bx bx-badge-check ci-amber"></i> ค่าคอม ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="inp_acc_comSale" type="text" class="form-control text-end money-input"
                      name="comSale" placeholder="0.00">
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : ช่วงเวลา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-calendar"></i>
              </div>
              <span class="mf-section-title">ช่วงเวลา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="inp_acc_startDate" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-emerald"></i> วันที่เริ่ม <span class="text-danger">*</span>
                  </label>
                  <input id="inp_acc_startDate" type="date"
                    class="form-control @error('startDate') is-invalid @enderror" name="startDate" required>
                  @error('startDate')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="inp_acc_endDate" class="mf-label form-label">
                    <i class="bx bx-calendar-x ci-emerald"></i> วันที่สิ้นสุด <span class="text-danger">*</span>
                  </label>
                  <input id="inp_acc_endDate" type="date"
                    class="form-control @error('endDate') is-invalid @enderror" name="endDate" required>
                  @error('endDate')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreAccessory">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
