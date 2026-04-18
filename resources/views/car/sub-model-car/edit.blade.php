<div class="modal fade editSubCar" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลรถรุ่นย่อย</h6>
            <small class="text-white mf-hd-sub">Edit Sub Model</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('model.sub-model.update', ['sub_model_car' => $sub->id]) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section : ข้อมูลรุ่นรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรุ่นรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-8">
                  <label for="edit_sub_model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-indigo"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="edit_sub_model_id" name="model_id"
                    class="form-select @error('model_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}" {{ $sub->model_id == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}
                      </option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="edit_sub_type_carOrder" class="mf-label form-label">
                    <i class="bx bx-list-ul ci-indigo"></i> ประเภท
                  </label>
                  <select id="edit_sub_type_carOrder" name="type_carOrder" class="form-select">
                    <option value="">— เลือกประเภท —</option>
                    @foreach ($typeCar as $item)
                      <option value="{{ @$item->id }}" {{ $sub->type_carOrder == $item->id ? 'selected' : '' }}>
                        {{ @$item->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <label for="edit_sub_name" class="mf-label form-label">
                    <i class="bx bx-font ci-indigo"></i> ชื่อรุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <input id="edit_sub_name" type="text" class="form-control @error('name') is-invalid @enderror"
                    name="name" value="{{ $sub->name }}" autocomplete="off" required>
                  @error('name')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Section : รายละเอียด --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-notepad"></i>
              </div>
              <span class="mf-section-title">รายละเอียด</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="edit_sub_detail" class="mf-label form-label">
                    <i class="bx bx-align-left ci-emerald"></i> รายละเอียด
                  </label>
                  <textarea id="edit_sub_detail" class="form-control @error('detail') is-invalid @enderror" name="detail" rows="3">{{ $sub->detail }}</textarea>
                  @error('detail')
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
            <button type="button" class="btn btn-primary px-5 btnUpdateSubCar">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
