@extends('layouts/contentNavbarLayout')
@section('title', 'บันทึกการใช้ฟิล์ม')

@section('page-script')
@vite(['resources/assets/js/film-usage.js'])
@endsection

@section('page-style')
<style>
  /* ── Film Usage create — page polish (scoped) ──────────────── */
  .fu-page { border: 0; box-shadow: 0 2px 14px rgba(16, 24, 40, .06); }
  .fu-page .mf-section { transition: box-shadow .2s ease, border-color .2s ease; }
  .fu-page .mf-section:hover { box-shadow: 0 6px 18px rgba(16, 24, 40, .08); }

  /* section icon variants not in base css */
  .fu-page .mf-section-icon.purple { background: #f3e8ff; color: #9333ea; }
  .fu-page .mf-section-icon.teal   { background: #ccfbf1; color: #0d9488; }

  /* inputs */
  .fu-page .form-control:focus,
  .fu-page .form-select:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 .18rem rgba(99, 102, 241, .15);
  }

  /* flatpickr ใส่ไอคอนปฏิทินใน .input-group — กันไม่ให้ไอคอนตกบรรทัดในคอลัมน์แคบ */
  .fu-page .input-group { flex-wrap: nowrap; }
  .fu-page .input-group > .form-control { min-width: 0; }

  /* ── Installation-type selector cards ── */
  .fu-type-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    max-width: 540px;
  }
  .fu-type-card {
    display: flex; align-items: center; gap: 13px;
    margin: 0; padding: 14px 16px;
    border: 2px solid #e6eaf2; border-radius: 13px;
    background: #fff; cursor: pointer;
    transition: all .18s ease;
  }
  .fu-type-card:hover { border-color: #cbd5e1; transform: translateY(-1px); }
  .fu-type-ic {
    width: 44px; height: 44px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; background: #f1f5f9; color: #64748b;
    transition: all .18s ease;
  }
  .fu-type-title { font-weight: 700; font-size: .95rem; color: #1e293b; line-height: 1.15; }
  .fu-type-sub   { font-size: .74rem; color: #94a3b8; }
  .btn-check:checked + .fu-type-card.is-general { border-color: #0ea5e9; background: #f0f9ff; }
  .btn-check:checked + .fu-type-card.is-general .fu-type-ic { background: #0ea5e9; color: #fff; }
  .btn-check:checked + .fu-type-card.is-bp { border-color: #f59e0b; background: #fffbeb; }
  .btn-check:checked + .fu-type-card.is-bp .fu-type-ic { background: #f59e0b; color: #fff; }
  .btn-check:focus-visible + .fu-type-card { outline: 2px solid #818cf8; outline-offset: 2px; }

  /* ── Fields flow into the same row as วันที่สั่งงาน ──
     display:contents ทำให้ลูกไม่ใช่ direct child ของ .row จึงต้องจำลอง .row > * เอง */
  .fu-page .fu-inline-fields { display: contents; }
  .fu-page .fu-inline-fields > * {
    flex-shrink: 0;
    max-width: 100%;
    margin-top: var(--bs-gutter-y);
    padding-right: calc(var(--bs-gutter-x) * .5);
    padding-left: calc(var(--bs-gutter-x) * .5);
  }
  /* มือถือ (< md): ไม่มี col-md-* คุมความกว้าง จึงให้เต็มแถว
     (วางใน media query เพื่อไม่ให้ specificity ไปทับ col-md-* ตอนจอ ≥ md) */
  @media (max-width: 767.98px) {
    .fu-page .fu-inline-fields > * { width: 100%; }
  }

  /* ── Customer-source highlight strip (BP) ── */
  .fu-source-box {
    background: #f8fafc;
    border: 1px dashed #d8dee9;
    border-radius: 11px;
    padding: 13px 15px;
  }

  /* ── Sub-heading inside a section ── */
  .fu-page .fu-subhead {
    display: flex; align-items: center; gap: 6px;
    font-size: .82rem; font-weight: 700; color: #475569; margin-bottom: 10px;
  }
  .fu-page .fu-subhead i { color: #6366f1; font-size: 1.05rem; }

  /* ── Film-brand / package grouping panel ── */
  .fu-pkg-panel {
    background: #fafbff;
    border: 1px solid #eef0f7;
    border-radius: 12px;
    padding: 14px 16px;
  }

  /* ── Package selectable cards ── */
  .fu-pkg-card {
    display: inline-flex; align-items: center; gap: 8px; margin: 0;
    padding: 10px 16px; border: 1.5px solid #e6eaf2; border-radius: 11px;
    background: #fff; color: #475569; font-weight: 600; font-size: .9rem;
    cursor: pointer; transition: all .16s ease;
  }
  .fu-pkg-card i { font-size: 1.05rem; color: #94a3b8; transition: color .16s ease; }
  .fu-pkg-card:hover { border-color: #c7d2fe; background: #f8faff; }
  .btn-check:checked + .fu-pkg-card { border-color: #6366f1; background: #eef2ff; color: #4338ca; }
  .btn-check:checked + .fu-pkg-card i { color: #6366f1; }
  .btn-check:checked + .fu-pkg-card.is-warm { border-color: #f59e0b; background: #fffbeb; color: #b45309; }
  .btn-check:checked + .fu-pkg-card.is-warm i { color: #f59e0b; }
  .btn-check:focus-visible + .fu-pkg-card { outline: 2px solid #818cf8; outline-offset: 2px; }

  /* ── BP: ปุ่ม 1 บาน / 2 บาน ── */
  .fu-page .fu-pane-group { gap: 5px; }
  .fu-page .fu-pane-group > .btn { margin-left: 0; }
  .fu-page .fu-pane-btn {
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-weight: 600;
    font-size: .76rem;
    padding: 3px 12px;
    border-radius: 8px !important;
    display: inline-flex;
    align-items: center;
    transition: all .15s ease;
  }
  .fu-page .fu-pane-btn i { font-size: .9rem; }
  .fu-page .fu-pane-btn:hover { border-color: #c7d2fe; color: #4f46e5; background: #f8faff; }
  .fu-page .fu-pane-btn.active {
    background: #6366f1;
    border-color: #6366f1;
    color: #fff;
    box-shadow: 0 2px 6px rgba(99, 102, 241, .3);
  }

  /* ── Add-on toggle chips ── */
  .fu-addon-chip {
    display: inline-flex; align-items: center; gap: 6px; margin: 0;
    padding: 7px 14px; border: 1.5px dashed #cbd5e1; border-radius: 20px;
    background: #fff; color: #64748b; font-weight: 600; font-size: .82rem;
    cursor: pointer; transition: all .16s ease;
  }
  .fu-addon-chip:hover { border-color: #94a3b8; background: #f8fafc; }
  .btn-check:checked + .fu-addon-chip { border-style: solid; }
  .btn-check:checked + .fu-addon-chip.addon-warn { border-color: #f59e0b; background: #fffbeb; color: #b45309; }
  .btn-check:checked + .fu-addon-chip.addon-info { border-color: #0ea5e9; background: #f0f9ff; color: #0369a1; }
  .btn-check:checked + .fu-addon-chip.addon-primary { border-color: #6366f1; background: #eef2ff; color: #4338ca; }

  /* ── Detail table ── */
  .fu-page #positionTable thead th {
    background: #f8fafc; color: #475569; font-size: .8rem; white-space: nowrap;
  }
  .fu-page #positionTable tbody td { vertical-align: middle; }
  .fu-page #positionTable tbody tr:hover td { background: #fbfcfe; }
  .fu-page #positionTable tfoot td {
    background: #eef2ff !important; color: #312e81; font-size: .92rem;
  }

  /* ── Sticky action bar ── */
  .fu-actions {
    position: sticky; bottom: 0;
    background: #fff;
    border-top: 1px solid #eef1f6;
    padding: 14px 0 4px;
    margin-top: 4px;
    z-index: 3;
  }

  @media (max-width: 575.98px) {
    .fu-type-grid { grid-template-columns: 1fr; }
  }
</style>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card fu-page">

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
          <a href="{{ route('film-usage.index') }}" class="btn btn-sm btn-danger">
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
              <div class="fu-type-grid">
                <input type="radio" class="btn-check" name="type" id="typeGeneral" value="general" checked>
                <label class="fu-type-card is-general" for="typeGeneral">
                  <span class="fu-type-ic"><i class="bx bx-car"></i></span>
                  <span>
                    <span class="fu-type-title d-block">ทั่วไป</span>
                    <span class="fu-type-sub">ติดฟิล์มรถใหม่ (ค้นหาจาก VIN)</span>
                  </span>
                </label>
                <input type="radio" class="btn-check" name="type" id="typeBP" value="bp">
                <label class="fu-type-card is-bp" for="typeBP">
                  <span class="fu-type-ic"><i class="bx bx-wrench"></i></span>
                  <span>
                    <span class="fu-type-title d-block">BP</span>
                    <span class="fu-type-sub">งานซ่อมสี / ตัวถัง</span>
                  </span>
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
                <div id="generalVinSection" class="col-md-4">
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

                {{-- Hidden fields --}}
                <input type="hidden" name="car_order_id" id="fu_car_order_id">
                <input type="hidden" name="salecar_id" id="fu_salecar_id">
                <input type="hidden" name="model_id" id="fu_model_id">

                {{-- ทั่วไป: ข้อมูลที่ auto-fill จาก VIN --}}
                <div id="generalInfoFields" class="col-12 d-none">
                  <div class="customer-info-row">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <div class="po-label"><i class="bx bxs-user"></i> ชื่อ-สกุล ลูกค้า</div>
                        <div class="info-val" id="fu_customer_name_display">—</div>
                        <input type="hidden" name="customer_name" id="fu_customer_name">
                      </div>
                      <div class="col-md-4">
                        <div class="po-label"><i class="bx bx-briefcase"></i> ฝ่ายขาย</div>
                        <div class="info-val" id="fu_sale_person_display">—</div>
                        <input type="hidden" name="sale_person" id="fu_sale_person">
                      </div>
                      <div class="col-md-4">
                        <div class="po-label"><i class="bx bx-car"></i> รุ่นรถ</div>
                        <div class="info-val fw-bold text-primary" id="fu_model_display">—</div>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- ทั่วไป (ลูกค้าใหม่): กรอกเอง — ไหลเข้าแถวเดียวกับวันที่สั่งงาน --}}
                <div id="newCustomerFields" class="fu-inline-fields d-none">
                  <div class="col-md-3">
                    <label for="fu_vin_new" class="mf-label form-label">
                      <i class="bx bx-barcode-reader"></i> เลข VIN <span class="text-danger">*</span>
                    </label>
                    <input id="fu_vin_new" type="text" class="form-control text-uppercase"
                      placeholder="ระบุเลข VIN" autocomplete="off">
                  </div>
                  <div class="col-md-3">
                    <label for="fu_customer_name_new" class="mf-label form-label">
                      <i class="bx bx-user"></i> ชื่อ-สกุล ลูกค้า <span class="text-danger">*</span>
                    </label>
                    <input id="fu_customer_name_new" type="text" class="form-control" placeholder="ชื่อ-สกุล ลูกค้า">
                  </div>
                  <div class="col-md-3">
                    <label for="fu_sale_person_new" class="mf-label form-label">
                      <i class="bx bx-briefcase"></i> ฝ่ายขาย
                    </label>
                    <select id="fu_sale_person_new" class="form-select">
                      <option value="">— เลือกฝ่ายขาย —</option>
                      @foreach ($saleUsers as $su)
                        <option value="{{ $su->name }}">{{ $su->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="fu_model_id_new" class="mf-label form-label">
                      <i class="bx bx-car"></i> รุ่นรถ
                    </label>
                    <select id="fu_model_id_new" class="form-select">
                      <option value="">— เลือกรุ่นรถ —</option>
                      @foreach ($models as $m)
                        <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                {{-- BP: กรอกข้อมูลลูกค้า (display:contents → ไหลเข้าแถวเดียวกับวันที่สั่งงาน) --}}
                <div id="bpInfoFields" class="fu-inline-fields d-none">
                  <div class="col-md-3">
                    <label for="fu_customer_name_bp" class="mf-label form-label">
                      <i class="bx bx-user"></i> ชื่อ-สกุล ลูกค้า <span class="text-danger">*</span>
                    </label>
                    <input id="fu_customer_name_bp" type="text"
                      class="form-control" placeholder="ชื่อ-สกุล ลูกค้า">
                  </div>
                  <div id="bpVinSection" class="col-md-3 d-none">
                    <label class="mf-label form-label text-muted">
                      <i class="bx bx-barcode-reader"></i> เลข VIN (ถ้ามี)
                    </label>
                    <input type="text" name="vin_bp" class="form-control text-uppercase"
                      placeholder="ระบุเลข VIN (ไม่บังคับ)" autocomplete="off">
                  </div>
                  <div class="col-md-3">
                    <label for="fu_car_brand_bp" class="mf-label form-label">
                      <i class="bx bx-car"></i> ยี่ห้อ <span class="text-danger">*</span>
                    </label>
                    <select id="fu_car_brand_bp" class="form-select">
                      <option value="">— กรุณาเลือกยี่ห้อ —</option>
                      @foreach ($carBrands as $cb)
                        <option value="{{ $cb->name }}">{{ $cb->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="fu_car_model_bp" class="mf-label form-label">รุ่นรถ</label>
                    <input id="fu_car_model_bp" type="text" class="form-control" placeholder="ระบุรุ่นรถ">
                  </div>
                  <div class="col-md-2">
                    <label for="fu_car_year_bp" class="mf-label form-label">ปีรถ</label>
                    <input id="fu_car_year_bp" type="text" class="form-control" inputmode="numeric"
                      maxlength="4" placeholder="เช่น 2024">
                  </div>
                  <div class="col-md-3">
                    <label for="fu_source_bp" class="mf-label form-label">
                      <i class="bx bx-user-voice"></i> แหล่งที่มาลูกค้า <span class="text-danger">*</span>
                    </label>
                    <select id="fu_source_bp" name="customer_source" class="form-select">
                      <option value="self">ลูกค้ามาด้วยตัวเอง</option>
                      <option value="insurance">ลูกค้าประกัน</option>
                    </select>
                  </div>
                  <div class="col-md-4 d-none" id="bpInsuranceWrap">
                    <label for="fu_insurance_bp" class="mf-label form-label">
                      <i class="bx bx-shield-quarter"></i> ประกัน <span class="text-danger">*</span>
                    </label>
                    <select id="fu_insurance_bp" class="form-select">
                      <option value="">— กรุณาเลือกประกัน —</option>
                      @foreach ($insurances as $ins)
                        <option value="{{ $ins->name }}">{{ $ins->name }}</option>
                      @endforeach
                    </select>
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
                <div class="fu-pkg-panel">
                  <div class="fu-subhead"><i class="bx bx-package"></i> เลือกแพ็กเกจ</div>
                  <div class="d-flex gap-2 flex-wrap">
                    <input type="radio" class="btn-check" name="package" id="pkgFull" value="full" autocomplete="off">
                    <label class="fu-pkg-card" for="pkgFull">
                      <i class="bx bx-car"></i> ทั้งคัน
                    </label>

                    <input type="radio" class="btn-check" name="package" id="pkgFrontBody" value="front_body" autocomplete="off">
                    <label class="fu-pkg-card" for="pkgFrontBody">
                      <i class="bx bx-window"></i> บานหน้า + รอบคัน
                    </label>

                    <input type="radio" class="btn-check" name="package" id="pkgAdvanced" value="advanced" autocomplete="off">
                    <label class="fu-pkg-card" for="pkgAdvanced">
                      <i class="bx bx-customize"></i> ขั้นสูง
                    </label>

                    {{-- แพ็กเกจเดี่ยว แสดงเมื่อมีข้อมูลในหน้าราคาฟิล์ม --}}
                    <span id="pkgSunroofWrap" class="d-none">
                      <input type="radio" class="btn-check" name="package" id="pkgSunroof" value="sunroof" autocomplete="off">
                      <label class="fu-pkg-card is-warm" for="pkgSunroof">
                        <i class="bx bx-sun"></i> ซันรูฟ
                      </label>
                    </span>

                    <span id="pkg3windowWrap" class="d-none">
                      <input type="radio" class="btn-check" name="package" id="pkg3window" value="window3" autocomplete="off">
                      <label class="fu-pkg-card" for="pkg3window">
                        <i class="bx bx-layer"></i> แพ็กเกจ 3 บาน
                      </label>
                    </span>
                  </div>

                  <div class="d-flex flex-wrap align-items-center gap-2 mt-3" id="addonChipRow">
                    <div id="sunroofToggleRow" class="d-none">
                      <input type="checkbox" class="btn-check" id="addSunroof" autocomplete="off">
                      <label class="fu-addon-chip addon-warn" for="addSunroof">
                        <i class="bx bx-sun"></i> เพิ่มซันรูฟ
                      </label>
                    </div>
                    <div id="doorRear2ToggleRow" class="d-none">
                      <input type="checkbox" class="btn-check" id="addDoorRear2" autocomplete="off">
                      <label class="fu-addon-chip addon-info" for="addDoorRear2">
                        <i class="bx bx-window-alt"></i> เพิ่มกระจกประตูคู่หลัง 2
                      </label>
                    </div>
                    <div id="window3ToggleRow" class="d-none">
                      <input type="checkbox" class="btn-check" id="add3window" autocomplete="off">
                      <label class="fu-addon-chip addon-primary" for="add3window">
                        <i class="bx bx-layer"></i> เพิ่มแพ็กเกจ 3 บาน
                      </label>
                    </div>
                  </div>
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
              {{-- BP: ปุ่มเพิ่มแพ็กเกจ (เฉพาะ BP) --}}
              <div id="bpPositionSection" class="d-none ms-auto">
                <button type="button" class="btn btn-primary btn-sm px-3" id="btnAddBpRow">
                  <i class="bx bx-plus me-1"></i> เพิ่มแพ็กเกจ
                </button>
              </div>
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
          <div class="fu-actions d-flex justify-content-end gap-2">
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
