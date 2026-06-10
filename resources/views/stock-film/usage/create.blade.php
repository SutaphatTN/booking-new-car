@extends('layouts/contentNavbarLayout')
@section('title', 'บันทึกการใช้ฟิล์ม')

@section('page-script')
@vite(['resources/assets/js/film-usage.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- Page Header --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-film fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">บันทึกการใช้ฟิล์ม</div>
          <div class="text-white mf-hd-sub">Film Usage Record</div>
        </div>
        <div class="ms-auto">
          <a href="{{ route('film-usage.index') }}" class="btn btn-sm btn-light opacity-75">
            <i class="bx bx-arrow-back me-1"></i> กลับ
          </a>
        </div>
      </div>

      <div class="card-body pt-4">
        <form id="formFilmUsage">
          @csrf

          {{-- Section 1: ประเภทการติดตั้ง --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon purple">
                <i class="bx bx-category"></i>
              </div>
              <span class="mf-section-title">ประเภทการติดตั้ง</span>
            </div>
            <div class="mf-section-body">
              <div class="d-flex gap-2">
                <input type="radio" class="btn-check" name="type" id="typeGeneral" value="general" checked>
                <label class="btn btn-outline-info px-4" for="typeGeneral">
                  <i class="bx bx-car me-1"></i> ทั่วไป
                </label>
                <input type="radio" class="btn-check" name="type" id="typeBP" value="bp">
                <label class="btn btn-outline-warning px-4" for="typeBP">
                  <i class="bx bx-wrench me-1"></i> BP
                </label>
              </div>
            </div>
          </div>

          {{-- Section 2: ข้อมูลทั่วไป --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-info-circle"></i>
              </div>
              <span class="mf-section-title">ข้อมูลทั่วไป</span>
              <div class="ms-auto d-none" id="btnNewCustomerWrap">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnNewCustomer">
                  <i class="bx bx-user-plus me-1"></i> ลูกค้าใหม่
                </button>
              </div>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="fu_order_date" class="mf-label form-label">
                    <i class="bx bx-calendar"></i> วันที่สั่งงาน <span class="text-danger">*</span>
                  </label>
                  <input id="fu_order_date" type="date" name="order_date" class="form-control" required
                    value="{{ date('Y-m-d') }}">
                </div>

                {{-- ทั่วไป: VIN Search (autocomplete) --}}
                <div id="generalVinSection" class="col-md-9">
                  <label for="fu_vin" class="mf-label form-label">
                    <i class="bx bx-barcode-reader"></i> เลข VIN <span class="text-danger">*</span>
                  </label>
                  <div class="position-relative">
                    <input id="fu_vin" type="text" name="vin" class="form-control text-uppercase"
                      placeholder="พิมพ์อย่างน้อย 3 ตัวเพื่อค้นหา..." autocomplete="off">
                    <div id="vinSearchSpinner" class="position-absolute top-50 end-0 translate-middle-y pe-3 d-none">
                      <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                  </div>
                  <ul id="vinSuggestList" class="list-group mt-1 d-none shadow-sm" style="max-height:230px;overflow-y:auto;"></ul>
                  <div id="vinSearchStatus" class="small mt-1 d-none"></div>
                </div>

                {{-- BP: VIN ไม่บังคับ --}}
                <div id="bpVinSection" class="col-md-9 d-none">
                  <label class="mf-label form-label text-muted">
                    <i class="bx bx-barcode-reader"></i> เลข VIN (ถ้ามี)
                  </label>
                  <input type="text" name="vin_bp" class="form-control text-uppercase"
                    placeholder="ระบุเลข VIN (ไม่บังคับ)" autocomplete="off">
                </div>

                {{-- Hidden fields --}}
                <input type="hidden" name="car_order_id" id="fu_car_order_id">
                <input type="hidden" name="salecar_id" id="fu_salecar_id">
                <input type="hidden" name="model_id" id="fu_model_id">

                {{-- ทั่วไป: ข้อมูลที่ auto-fill จาก VIN --}}
                <div id="generalInfoFields" class="col-12 d-none">
                  <div class="rounded-2 border bg-light px-4 py-3">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bx bx-user ci-indigo me-1"></i>ชื่อ-สกุล ลูกค้า</div>
                        <input id="fu_customer_name_display" type="text" class="form-control-plaintext fw-semibold p-0" readonly>
                        <input type="hidden" name="customer_name" id="fu_customer_name">
                      </div>
                      <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bx bx-briefcase ci-sky me-1"></i>ฝ่ายขาย</div>
                        <input id="fu_sale_person_display" type="text" class="form-control-plaintext fw-semibold p-0" readonly>
                        <input type="hidden" name="sale_person" id="fu_sale_person">
                      </div>
                      <div class="col-md-4">
                        <div class="small text-muted mb-1"><i class="bx bx-car ci-amber me-1"></i>รุ่นรถ</div>
                        <input id="fu_model_display" type="text" class="form-control-plaintext fw-bold text-primary p-0" readonly>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- ทั่วไป (ลูกค้าใหม่): กรอกเอง --}}
                <div id="newCustomerFields" class="col-12 d-none">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label for="fu_vin_new" class="mf-label form-label">
                        <i class="bx bx-barcode-reader"></i> เลข VIN <span class="text-danger">*</span>
                      </label>
                      <input id="fu_vin_new" type="text" class="form-control text-uppercase"
                        placeholder="ระบุเลข VIN" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                      <label for="fu_customer_name_new" class="mf-label form-label">
                        ชื่อ-สกุล ลูกค้า <span class="text-danger">*</span>
                      </label>
                      <input id="fu_customer_name_new" type="text" class="form-control" placeholder="ชื่อ-สกุล ลูกค้า">
                    </div>
                    <div class="col-md-3">
                      <label for="fu_sale_person_new" class="mf-label form-label">ฝ่ายขาย</label>
                      <input id="fu_sale_person_new" type="text" class="form-control" placeholder="ชื่อฝ่ายขาย">
                    </div>
                    <div class="col-md-3">
                      <label for="fu_model_id_new" class="mf-label form-label">รุ่นรถ</label>
                      <select id="fu_model_id_new" class="form-select">
                        <option value="">— เลือกรุ่นรถ —</option>
                        @foreach ($models as $m)
                          <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>

                {{-- BP: กรอกข้อมูลลูกค้า --}}
                <div id="bpInfoFields" class="col-12 d-none">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label for="fu_customer_name_bp" class="mf-label form-label">
                        ชื่อ-สกุล ลูกค้า <span class="text-danger">*</span>
                      </label>
                      <input id="fu_customer_name_bp" type="text" name="customer_name"
                        class="form-control" placeholder="ชื่อ-สกุล ลูกค้า">
                    </div>
                    <div class="col-md-4">
                      <label for="fu_sale_person_bp" class="mf-label form-label">ฝ่ายขาย</label>
                      <select id="fu_sale_person_bp" name="sale_person" class="form-select">
                        <option value="">— เลือกฝ่ายขาย —</option>
                        @foreach ($saleUsers as $su)
                          <option value="{{ $su->name }}">{{ $su->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="fu_model_id_bp" class="mf-label form-label">รุ่นรถ</label>
                      <select id="fu_model_id_bp" name="model_id_bp" class="form-select">
                        <option value="">— เลือกรุ่นรถ —</option>
                        @foreach ($models as $m)
                          <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3: ข้อมูลฟิล์ม + ตำแหน่ง --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-layer"></i>
              </div>
              <span class="mf-section-title">ข้อมูลฟิล์ม & ตำแหน่ง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3 mb-4">

                {{-- ยี่ห้อฟิล์ม --}}
                <div class="col-md-4">
                  <label for="fu_film_brand_id" class="mf-label form-label">
                    <i class="bx bx-layer"></i> ยี่ห้อฟิล์ม <span class="text-danger">*</span>
                  </label>
                  <select id="fu_film_brand_id" name="film_brand_id" class="form-select">
                    <option value="">— เลือกยี่ห้อ —</option>
                    @foreach ($filmBrands as $fb)
                      <option value="{{ $fb->id }}" data-code="{{ $fb->code }}">{{ $fb->name }}</option>
                    @endforeach
                  </select>
                </div>

              </div>

              {{-- ทั่วไป: Package selector --}}
              <div id="generalPackageSection">
                <div class="mf-label fw-bold mb-2">แพ็กเกจ</div>
                <div class="d-flex gap-2 flex-wrap">
                  <input type="radio" class="btn-check" name="package" id="pkgFull" value="full" autocomplete="off">
                  <label class="btn btn-outline-secondary" for="pkgFull">
                    <i class="bx bx-car me-1"></i> ทั้งคัน
                  </label>

                  <input type="radio" class="btn-check" name="package" id="pkgFrontBody" value="front_body" autocomplete="off">
                  <label class="btn btn-outline-secondary" for="pkgFrontBody">
                    <i class="bx bx-window me-1"></i> บานหน้า + รอบคัน
                  </label>

                  <input type="radio" class="btn-check" name="package" id="pkgAdvanced" value="advanced" autocomplete="off">
                  <label class="btn btn-outline-secondary" for="pkgAdvanced">
                    <i class="bx bx-customize me-1"></i> ขั้นสูง
                  </label>
                </div>

                <div id="sunroofToggleRow" class="d-none mt-3">
                  <input type="checkbox" class="btn-check" id="addSunroof" autocomplete="off">
                  <label class="btn btn-outline-warning btn-sm" for="addSunroof">
                    <i class="bx bx-sun me-1"></i> เพิ่มซันรูฟ
                  </label>
                </div>
              </div>

              {{-- BP: Position checkboxes --}}
              <div id="bpPositionSection" class="d-none">
                <div class="mf-label fw-bold mb-2">เลือกตำแหน่ง</div>
                <div class="d-flex flex-wrap gap-2">
                  @foreach (['บานหน้า','บานหลัง','กระจกประตูคู่หน้า','กระจกประตูคู่หลัง 1','กระจกประตูคู่หลัง 2','กระจกหูช้าง','ซันรูฟ'] as $pos)
                    <input type="checkbox" class="btn-check bpPosCheck"
                      id="bp_pos_{{ $loop->index }}" value="{{ $pos }}" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="bp_pos_{{ $loop->index }}">
                      @if ($pos === 'ซันรูฟ')
                        <i class="bx bx-sun me-1"></i>
                      @endif
                      {{ $pos }}
                    </label>
                  @endforeach
                </div>
              </div>

            </div>
          </div>

          {{-- Section 4: รายละเอียด --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-table"></i>
              </div>
              <span class="mf-section-title">รายละเอียด</span>
            </div>
            <div class="mf-section-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered mb-0" id="positionTable">
                  <thead class="table-light">
                    <tr>
                      <th style="min-width:180px">ตำแหน่ง</th>
                      <th style="min-width:100px">ความเข้ม</th>
                      <th style="min-width:200px">Stock No.</th>
                      <th class="text-end" style="min-width:100px">ตร.ฟุต</th>
                      <th class="text-end" style="min-width:130px">ราคาขาย (฿)</th>
                      <th class="text-end" style="min-width:120px">ค่าคอม (฿)</th>
                    </tr>
                  </thead>
                  <tbody id="positionRows">
                    <tr id="noRowMsg">
                      <td colspan="6" class="text-center text-muted py-4">
                        <i class="bx bx-info-circle fs-5 d-block mb-1"></i>
                        เลือกแพ็กเกจหรือตำแหน่งก่อน
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="table-light fw-bold">
                      <td colspan="3" class="text-end pe-3">รวมทั้งหมด</td>
                      <td class="text-end" id="totalSqft">-</td>
                      <td class="text-end" id="totalPrice">-</td>
                      <td class="text-end" id="totalCommission">-</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-2">
            <a href="{{ route('film-usage.index') }}" class="btn btn-danger px-4">
              <i class="bx bx-x me-1"></i> ยกเลิก
            </a>
            <button type="button" class="btn btn-primary px-5 btnSaveFilmUsage">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection
