<div class="modal fade inputGwmIncentive" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-trending-up fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูล GWM Incentive</h6>
            <small class="text-white mf-hd-sub">Add GWM Incentive</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('gwm-incentive.store') }}" method="POST">
          @csrf

          {{-- Section: รุ่นรถ + เดือน/ปี --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo"><i class="bx bx-car"></i></div>
              <span class="mf-section-title">ข้อมูลรุ่นรถ & เดือน/ปี</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="inc_model_id" name="model_id" class="form-select" required
                    {{ $preSub ? 'disabled' : '' }}>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}"
                        {{ $preSub && $preSub->model_id == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_EN }}
                      </option>
                    @endforeach
                  </select>
                  @if ($preSub)
                    <input type="hidden" name="model_id" value="{{ $preSub->model_id }}">
                  @endif
                </div>

                <div class="col-md-4">
                  <label class="mf-label form-label">
                    <i class="bx bx-subdirectory-right"></i> รุ่นรถย่อย <span class="text-danger">*</span>
                  </label>
                  <select id="inc_subcarmodel_id" name="subcarmodel_id" class="form-select" required
                    {{ $preSub ? 'disabled' : ($preSubModels->isEmpty() ? 'disabled' : '') }}>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                    @foreach ($preSubModels as $s)
                      <option value="{{ $s->id }}" {{ $preSub && $preSub->id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                      </option>
                    @endforeach
                  </select>
                  @if ($preSub)
                    <input type="hidden" name="subcarmodel_id" value="{{ $preSub->id }}">
                  @endif
                </div>

                <div class="col-md-2">
                  <label class="mf-label form-label">
                    <i class="bx bx-calendar"></i> เดือน <span class="text-danger">*</span>
                  </label>
                  <select name="month" class="form-select" required>
                    @foreach ($months as $num => $name)
                      <option value="{{ $num }}" {{ $num == $preMonth ? 'selected' : '' }}>
                        {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="mf-label form-label">
                    <i class="bx bx-calendar-check"></i> ปี (ค.ศ.) <span class="text-danger">*</span>
                  </label>
                  <input type="number" class="form-control" name="year"
                    value="{{ $preYear }}" min="2020" max="2099" required>
                </div>

              </div>
            </div>
          </div>

          {{-- Section: Incentive --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-bar-chart-alt-2"></i></div>
              <span class="mf-section-title">Incentive ตามยอดขาย (%)</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                @foreach ([
                  ['name' => 'fixed',        'label' => 'Fixed %'],
                  ['name' => 'lt70',         'label' => '&lt;70%'],
                  ['name' => 'gte70_lte85',  'label' => '70%≤x≤85%'],
                  ['name' => 'gt85_lte100',  'label' => '85%&lt;x≤100%'],
                  ['name' => 'gt100_lte120', 'label' => '100%&lt;x≤120%'],
                  ['name' => 'gte120',       'label' => 'x≥120%'],
                  ['name' => 'max_val',      'label' => 'Max'],
                ] as $field)
                  <div class="col-md-2">
                    <label class="mf-label form-label">{!! $field['label'] !!}</label>
                    <div class="input-group">
                      <input type="number" step="0.01" min="0" max="100"
                        class="form-control text-end" name="{{ $field['name'] }}" value="0" required>
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreGwmIncentive">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
