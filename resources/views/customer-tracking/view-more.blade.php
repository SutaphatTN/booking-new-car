@extends('layouts/contentNavbarLayout')
@section('title', 'รายละเอียดการติดตาม')

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')

  @php
    $c = $tracking->customer;
    $isSale = auth()->user()->role === 'sale';
    $saleDetails = $tracking->details->where('entry_type', 'sale')->sortBy([['contact_date', 'desc'], ['id', 'desc']]);
    $managerDetails = $tracking->details
        ->where('entry_type', 'manager')
        ->sortBy([['contact_date', 'desc'], ['id', 'desc']]);
    $fullName = trim(($c->prefix->Name_TH ?? '') . ' ' . ($c->FirstName ?? '') . ' ' . ($c->LastName ?? ''));
    $totalDetails = $tracking->details->count();
    $hasLockedManagerDecision = $managerDetails->whereIn('decision_id', [1, 2])->isNotEmpty();
  @endphp

  {{-- Page Title --}}
  <div class="pur-page-title mb-3 justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <div class="pur-page-icon">
        <i class="bx bx-search-alt"></i>
      </div>
      <div>
        <h5 class="pur-page-name">รายละเอียดการติดตามลูกค้า</h5>
        <small class="text-muted" style="font-size:0.8rem;">{{ $fullName }}</small>
      </div>
    </div>
    <div class="d-flex gap-2 vm-header-btns">
      <a href="{{ route('customer-tracking.index') }}" class="btn btn-outline-danger">
        <i class="bx bx-arrow-back me-1"></i> ย้อนกลับ
      </a>
      <a href="{{ route('purchase-order.create', ['from_tracking' => $tracking->id]) }}"
        class="btn btn-secondary ms-auto">
        <i class="bx bx-file me-1"></i> สร้างการจอง
      </a>
    </div>
  </div>

  <div class="nav-align-top">
    {{-- Main Tabs (same style as purchase-order/edit) --}}
    <ul class="nav nav-pills mb-4 nav-fill" id="viewMoreTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">
          <span class="d-none d-sm-inline-flex align-items-center"><i
              class="icon-base bx bx-user icon-sm me-1_5"></i>ข้อมูลลูกค้า</span>
          <i class="icon-base bx bx-user icon-sm d-sm-none"></i>
        </button>
      </li>
      {{-- <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-grade" type="button" role="tab">
          <span class="d-none d-sm-inline-flex align-items-center"><i
              class="icon-base bx bx-star icon-sm me-1_5"></i>เกรด</span>
          <i class="icon-base bx bx-star icon-sm d-sm-none"></i>
        </button>
      </li> --}}
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab">
          <span class="d-none d-sm-inline-flex align-items-center"><i
              class="icon-base bx bx-notepad icon-sm me-1_5"></i>ประวัติการติดตาม</span>
          <i class="icon-base bx bx-notepad icon-sm d-sm-none"></i>
          @if ($totalDetails > 0)
            <span class="badge bg-label-primary rounded-pill ms-1">{{ $totalDetails }}</span>
          @endif
        </button>
      </li>
    </ul>

    <div class="tab-content">

      {{-- ===== TAB 1: ข้อมูลลูกค้า ===== --}}
      <div class="tab-pane fade show active" id="tab-info" role="tabpanel">

        <div class="row g-4">
          <div class="col-md-6">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
                <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
              </div>
              <div class="po-section-body mb-2">
                <div class="row g-4">
                  <div class="col-md-8">
                    <div class="po-label">ชื่อ - นามสกุล</div>
                    <div class="info-pill fw-semibold">{{ $fullName }}</div>
                  </div>
                  <div class="col-md-4">
                    <div class="po-label">เบอร์โทรศัพท์</div>
                    <div class="info-pill">{{ $c->formatted_mobile ?? ($c->Mobilephone1 ?? '-') }}</div>
                  </div>
                  {{-- <div class="col-md-6">
                    <div class="po-label">บัตรประชาชน</div>
                    <div class="info-pill">{{ $c->formatted_id_number ?? ($c->IDNumber ?? '-') }}</div>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">เบอร์โทรศัพท์</div>
                    <div class="info-pill">{{ $c->formatted_mobile ?? ($c->Mobilephone1 ?? '-') }}</div>
                  </div> --}}
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon indigo"><i class="bx bx-user-pin"></i></div>
                <h6 class="po-section-title">ข้อมูลผู้ขาย</h6>
              </div>
              <div class="po-section-body mb-2">
                <div class="row g-4">
                  <div class="col-md-6">
                    <div class="po-label">ผู้ขาย</div>
                    <div class="info-pill fw-semibold">{{ $tracking->sale->name ?? '-' }}</div>
                  </div>
                  <div class="col-md-6">
                    <div class="po-label">แหล่งที่มา</div>
                    <div class="info-pill">{{ $tracking->source->name ?? '-' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-12">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
                <h6 class="po-section-title">ข้อมูลรถที่สนใจ</h6>
              </div>
              <div class="po-section-body mb-2">
                <div class="row g-4">
                  @if ($tracking->brand == 1)
                    <div class="col-md-4">
                      <div class="po-label">รุ่นหลัก</div>
                      <div class="info-pill">{{ $tracking->model->Name_TH ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                      <div class="po-label">รุ่นย่อย</div>
                      <div class="info-pill">{{ $tracking->subModel->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">ประเภทสี</div>
                      <div class="info-pill">{{ $tracking->pricelist_color ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">ปี</div>
                      <div class="info-pill">{{ $tracking->year ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">Option</div>
                      <div class="info-pill">{{ $tracking->option ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                      <div class="po-label">สี</div>
                      <div class="info-pill">{{ $tracking->color_text ?? '-' }}</div>
                    </div>
                  @elseif($tracking->brand == 2)
                    <div class="col-md-3">
                      <div class="po-label">รุ่นหลัก</div>
                      <div class="info-pill">{{ $tracking->model->Name_TH ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="po-label">รุ่นย่อย</div>
                      <div class="info-pill">{{ $tracking->subModel->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">ปี</div>
                      <div class="info-pill">{{ $tracking->year ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                      <div class="po-label">สีภายนอก</div>
                      <div class="info-pill">{{ $tracking->wuColor->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">สีภายใน</div>
                      <div class="info-pill">{{ $tracking->interiorColor->name ?? '-' }}</div>
                    </div>
                  @else
                    <div class="col-md-3">
                      <div class="po-label">รุ่นหลัก</div>
                      <div class="info-pill">{{ $tracking->model->Name_TH ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                      <div class="po-label">รุ่นย่อย</div>
                      <div class="info-pill">{{ $tracking->subModel->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                      <div class="po-label">ปี</div>
                      <div class="info-pill">{{ $tracking->year ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                      <div class="po-label">สี</div>
                      <div class="info-pill">{{ $tracking->wuColor->name ?? '-' }}</div>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          {{-- ===== Test Drive ===== --}}
          <div class="col-md-12">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon rose"><i class="bx bx-trip"></i></div>
                <h6 class="po-section-title">ทดลองขับ</h6>
              </div>
              <div class="po-section-body mb-2">
                <div class="row g-4">
                  <div class="col-md-3">
                    <label class="po-label" for="td_date"><i class="bx bx-calendar me-1"></i>วันที่ทดลองขับ</label>
                    <input type="date" id="td_date" class="form-control"
                      value="{{ $tracking->test_drive_date ?? '' }}">
                  </div>
                  <div class="col-md-9">
                    <label class="po-label" for="td_note"><i class="bx bx-comment me-1"></i>หมายเหตุ</label>
                    <textarea id="td_note" class="form-control" rows="2"
                      placeholder="หมายเหตุ...">{{ $tracking->test_drive_note ?? '' }}</textarea>
                  </div>
                </div>
                <div class="d-flex justify-content-end mt-3 mb-1">
                  <button type="button" class="btn btn-primary btn-sm px-4" id="btnSaveTestDrive"
                    data-tracking-id="{{ $tracking->id }}">
                    <i class="bx bx-save me-1"></i> บันทึก
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- ===== TAB 2: เกรด ===== --}}
      <div class="tab-pane fade" id="tab-grade" role="tabpanel">
        <div class="row g-4">

          {{-- Dropdowns --}}
          <div class="col-md-8">
            <div class="po-section-edit mb-0">
              <div class="po-section-header">
                <div class="po-section-icon amber"><i class="bx bx-star"></i></div>
                <h6 class="po-section-title">ข้อมูลการให้คะแนน</h6>
              </div>
              <div class="po-section-body">
                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="po-label" for="gs_delivery"><i class="bx bx-calendar-check me-1"></i>
                      ระยะเวลาส่งมอบ</label>
                    <select id="gs_delivery" class="form-select gs-select" data-field="delivery_timeline_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ภายใน 30 วัน" data-score="15"
                        {{ $tracking->delivery_timeline_scoring == 'ภายใน 30 วัน' ? 'selected' : '' }}>ภายใน 30 วัน
                      </option>
                      <option value="1-3 เดือน" data-score="10"
                        {{ $tracking->delivery_timeline_scoring == '1-3 เดือน' ? 'selected' : '' }}>1-3 เดือน</option>
                      <option value="ยังไม่มีกำหนด" data-score="0"
                        {{ $tracking->delivery_timeline_scoring == 'ยังไม่มีกำหนด' ? 'selected' : '' }}>ยังไม่มีกำหนด
                      </option>
                    </select>
                    <div class="mt-1" id="score_delivery"><span
                        class="badge bg-label-secondary gs-score-val">—</span></div>
                  </div>

                  <div class="col-md-6">
                    <label class="po-label" for="gs_testdrive"><i class="bx bx-car me-1"></i> การทดลองขับ</label>
                    <select id="gs_testdrive" class="form-select gs-select" data-field="test_drive_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ทดลองขับ" data-score="15"
                        {{ $tracking->test_drive_scoring == 'ทดลองขับ' ? 'selected' : '' }}>ทดลองขับ</option>
                      <option value="จองวันทดลองขับ" data-score="10"
                        {{ $tracking->test_drive_scoring == 'จองวันทดลองขับ' ? 'selected' : '' }}>จองวันทดลองขับ</option>
                      <option value="ไม่ได้ทดลองขับ" data-score="5"
                        {{ $tracking->test_drive_scoring == 'ไม่ได้ทดลองขับ' ? 'selected' : '' }}>ไม่ได้ทดลองขับ</option>
                      <option value="ปฎิเสธทดลองขับ" data-score="0"
                        {{ $tracking->test_drive_scoring == 'ปฎิเสธทดลองขับ' ? 'selected' : '' }}>ปฎิเสธทดลองขับ</option>
                    </select>
                    <div class="mt-1" id="score_testdrive"><span
                        class="badge bg-label-secondary gs-score-val">—</span></div>
                  </div>

                  <div class="col-md-4">
                    <label class="po-label" for="gs_occupation"><i class="bx bx-briefcase me-1"></i> อาชีพ</label>
                    <select id="gs_occupation" class="form-select gs-select" data-field="occupation_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ข้าราชการ" data-score="10"
                        {{ $tracking->occupation_scoring == 'ข้าราชการ' ? 'selected' : '' }}>ข้าราชการ</option>
                      <option value="รัฐวิสาหกิจ" data-score="10"
                        {{ $tracking->occupation_scoring == 'รัฐวิสาหกิจ' ? 'selected' : '' }}>รัฐวิสาหกิจ</option>
                      <option value="เจ้าของกิจการ" data-score="10"
                        {{ $tracking->occupation_scoring == 'เจ้าของกิจการ' ? 'selected' : '' }}>เจ้าของกิจการ</option>
                      <option value="พนักงานประจำ" data-score="10"
                        {{ $tracking->occupation_scoring == 'พนักงานประจำ' ? 'selected' : '' }}>พนักงานประจำ</option>
                      <option value="รับจ้างทั่วไป" data-score="5"
                        {{ $tracking->occupation_scoring == 'รับจ้างทั่วไป' ? 'selected' : '' }}>รับจ้างทั่วไป</option>
                      <option value="อาชีพเสี่ยง" data-score="5"
                        {{ $tracking->occupation_scoring == 'อาชีพเสี่ยง' ? 'selected' : '' }}>อาชีพเสี่ยง</option>
                      <option value="ไม่สามารถระบุได้" data-score="0"
                        {{ $tracking->occupation_scoring == 'ไม่สามารถระบุได้' ? 'selected' : '' }}>ไม่สามารถระบุได้
                      </option>
                    </select>
                    <div class="mt-1" id="score_occupation"><span
                        class="badge bg-label-secondary gs-score-val">—</span></div>
                  </div>

                  <div class="col-md-4">
                    <label class="po-label" for="gs_revenue"><i class="bx bx-money me-1"></i> รายได้
                      (บาท/เดือน)</label>
                    <select id="gs_revenue" class="form-select gs-select" data-field="revenue_scoring">
                      <option value="">— เลือก —</option>
                      <option value=">50000" data-score="15"
                        {{ $tracking->revenue_scoring == '>50000' ? 'selected' : '' }}>&gt; 50,000</option>
                      <option value="25000-50000" data-score="12"
                        {{ $tracking->revenue_scoring == '25000-50000' ? 'selected' : '' }}>25,000 – 50,000</option>
                      <option value="15000-25000" data-score="8"
                        {{ $tracking->revenue_scoring == '15000-25000' ? 'selected' : '' }}>15,000 – 25,000</option>
                      <option value="<15000" data-score="5"
                        {{ $tracking->revenue_scoring == '<15000' ? 'selected' : '' }}>&lt; 15,000</option>
                      <option value="ไม่แจ้ง" data-score="0"
                        {{ $tracking->revenue_scoring == 'ไม่แจ้ง' ? 'selected' : '' }}>ไม่แจ้ง</option>
                    </select>
                    <div class="mt-1" id="score_revenue"><span class="badge bg-label-secondary gs-score-val">—</span>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label class="po-label" for="gs_purchase"><i class="bx bx-credit-card me-1"></i>
                      ประเภทการซื้อ</label>
                    <select id="gs_purchase" class="form-select gs-select" data-field="purchase_type_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ซื้อสด" data-score="15"
                        {{ $tracking->purchase_type_scoring == 'ซื้อสด' ? 'selected' : '' }}>ซื้อสด</option>
                      <option value="ดาวน์สูง" data-score="15"
                        {{ $tracking->purchase_type_scoring == 'ดาวน์สูง' ? 'selected' : '' }}>ดาวน์สูง</option>
                      <option value="ดาวน์ต่ำ" data-score="5"
                        {{ $tracking->purchase_type_scoring == 'ดาวน์ต่ำ' ? 'selected' : '' }}>ดาวน์ต่ำ</option>
                    </select>
                    <div class="mt-1" id="score_purchase"><span
                        class="badge bg-label-secondary gs-score-val">—</span></div>
                  </div>

                  <div class="col-md-6">
                    <label class="po-label" for="gs_model"><i class="bx bx-cube me-1"></i> ความชัดเจนเรื่องรุ่น</label>
                    <select id="gs_model" class="form-select gs-select" data-field="model_interest_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ระบุร่นชัดเจน" data-score="10"
                        {{ $tracking->model_interest_scoring == 'ระบุร่นชัดเจน' ? 'selected' : '' }}>ระบุรุ่นชัดเจน
                      </option>
                      <option value="ระบุรุ่นไม่ชัดเจน" data-score="5"
                        {{ $tracking->model_interest_scoring == 'ระบุรุ่นไม่ชัดเจน' ? 'selected' : '' }}>
                        ระบุรุ่นไม่ชัดเจน</option>
                    </select>
                    <div class="mt-1" id="score_model"><span class="badge bg-label-secondary gs-score-val">—</span>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="po-label" for="gs_engagement"><i class="bx bx-message me-1"></i> การตอบสนอง</label>
                    <select id="gs_engagement" class="form-select gs-select" data-field="engagement_scoring">
                      <option value="">— เลือก —</option>
                      <option value="ตอบภายใน1ชม" data-score="20"
                        {{ $tracking->engagement_scoring == 'ตอบภายใน1ชม' ? 'selected' : '' }}>ตอบภายใน 1 ชั่วโมง
                      </option>
                      <option value="ตอบภายใน1วัน" data-score="15"
                        {{ $tracking->engagement_scoring == 'ตอบภายใน1วัน' ? 'selected' : '' }}>ตอบภายใน 1 วัน</option>
                      <option value="ตอบภายใน2วัน" data-score="5"
                        {{ $tracking->engagement_scoring == 'ตอบภายใน2วัน' ? 'selected' : '' }}>ตอบภายใน 2 วัน</option>
                      <option value="ตอบช้าไม่แน่นอน" data-score="0"
                        {{ $tracking->engagement_scoring == 'ตอบช้าไม่แน่นอน' ? 'selected' : '' }}>ตอบช้า / ไม่แน่นอน
                      </option>
                    </select>
                    <div class="mt-1" id="score_engagement"><span
                        class="badge bg-label-secondary gs-score-val">—</span></div>
                  </div>

                </div>

                <div class="d-flex justify-content-end mt-4 mb-2">
                  <button type="button" class="btn btn-primary px-4" id="btnSaveGrade"
                    data-tracking-id="{{ $tracking->id }}">
                    <i class="bx bx-save me-1"></i> บันทึกเกรด
                  </button>
                </div>
              </div>
            </div>
          </div>

          {{-- Grade Display --}}
          <div class="col-md-4">
            <div class="po-section-edit mb-0 text-center">
              <div class="po-section-header">
                <div class="po-section-icon sky"><i class="bx bx-trophy"></i></div>
                <h6 class="po-section-title">ผลเกรด</h6>
              </div>
              <div class="po-section-body py-4">
                <div id="gradeLetter"
                  style="font-size:6rem;font-weight:900;line-height:1;color:#9ca3af;transition:color .3s;">—</div>
                <div class="text-muted mt-2" style="font-size:.85rem;">คะแนนรวม</div>
                <div id="gradeTotal" style="font-size:1.5rem;font-weight:700;color:#374151;transition:color .3s;">—
                </div>
                <div class="mt-3 px-2">
                  <div class="progress" style="height:10px;border-radius:8px;">
                    <div class="progress-bar" id="gradeProgress" role="progressbar"
                      style="width:0%;background:#d1d5db;transition:width .4s,background .3s;border-radius:8px;"></div>
                  </div>
                  <div class="d-flex justify-content-between text-muted mt-1" style="font-size:.75rem;">
                    <span>D≥0</span><span>C≥40</span><span>B≥60</span><span>A≥80</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- ===== TAB 3: ประวัติการติดตาม ===== --}}
      <div class="tab-pane fade" id="tab-history" role="tabpanel">

        {{-- Sub-tab bar --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="vm-subtab-scroll">
            <ul class="nav nav-pills gap-2" style="flex-wrap:nowrap;min-width:max-content;" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#sub-sale" type="button"
                  role="tab">
                  <i class="bx bx-user me-1"></i> บันทึกเซลล์
                  <span class="badge bg-label-warning rounded-pill ms-1">{{ $saleDetails->count() }}</span>
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sub-manager" type="button"
                  role="tab">
                  <i class="bx bx-briefcase me-1"></i> บันทึกผู้จัดการ
                  <span class="badge bg-label-primary rounded-pill ms-1">{{ $managerDetails->count() }}</span>
                </button>
              </li>
            </ul>
          </div>
          <div class="d-flex gap-2 flex-shrink-0">
            @if ($isSale)
              <button type="button" class="btn btn-warning btn-sm btnOpenAddDetail" data-entry-type="sale">
                <i class="bx bx-plus me-1"></i> เพิ่มบันทึก
              </button>
            @else
              @if (!$hasLockedManagerDecision)
                <button type="button" class="btn btn-primary btn-sm btnOpenAddDetail" data-entry-type="manager"
                  id="btnOpenManagerDetail">
                  <i class="bx bx-plus me-1"></i> เพิ่มบันทึก
                </button>
              @endif
              <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancelTracking"
                data-id="{{ $tracking->id }}">
                <i class="bx bx-x-circle me-1"></i> ยกเลิกการติดตาม
              </button>
            @endif
          </div>
        </div>

        <div class="tab-content">

          {{-- Sub-tab: บันทึกเซลล์ --}}
          <div class="tab-pane fade show active" id="sub-sale" role="tabpanel">
            @forelse($saleDetails as $detail)
              @include('customer-tracking._detail-card', ['detail' => $detail, 'accentColor' => 'amber'])
            @empty
              <div class="text-center text-muted py-5">
                <i class="bx bx-notepad fs-2 d-block mb-2 opacity-50"></i>
                <small>ยังไม่มีบันทึกจากเซลล์</small>
              </div>
            @endforelse
          </div>

          {{-- Sub-tab: บันทึกผู้จัดการ --}}
          <div class="tab-pane fade" id="sub-manager" role="tabpanel">
            @forelse($managerDetails as $detail)
              @include('customer-tracking._detail-card', [
                  'detail' => $detail,
                  'accentColor' => 'indigo',
                  'showEdit' => !$isSale,
              ])
            @empty
              <div class="text-center text-muted py-5">
                <i class="bx bx-briefcase fs-2 d-block mb-2 opacity-50"></i>
                <small>ยังไม่มีบันทึกจากผู้จัดการ</small>
              </div>
            @endforelse
          </div>

        </div>
      </div>

    </div>

  </div>

  {{-- <div class="mt-3 mb-5">
    <a href="{{ route('customer-tracking.index') }}" class="btn btn-secondary btn-sm">
      <i class="bx bx-arrow-back me-1"></i> กลับ
    </a>
  </div> --}}

  {{-- Modal เพิ่มการติดตาม --}}
  <div class="modal fade" id="modalAddDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">
        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-notepad fs-5 text-white" id="modalAddDetailIcon"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title" id="modalAddDetailTitle">เพิ่มบันทึก</h6>
              <small class="text-white mf-hd-sub" id="modalAddDetailSub"></small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body mf-body">
          <input type="hidden" id="add_entry_type" value="">
          <div class="row g-4">
            <div class="col-md-5">
              <label class="mf-label form-label" for="add_contact_date">
                <i class="bx bx-calendar"></i> วันที่ติดต่อ
              </label>
              <input type="date" id="add_contact_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-7">
              <label class="mf-label form-label" for="add_decision_id">
                <i class="bx bx-target-lock"></i> สถานะการตัดสินใจ
              </label>
              <select id="add_decision_id" class="form-select">
                <option value="">— เลือก —</option>
                @foreach ($decisions as $d)
                  <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-12">
              <label class="mf-label form-label" for="addContactYes"><i class="bx bx-phone-call"></i>
                สถานะการติดต่อ</label>
              <div class="yn-group mt-1">
                <input type="radio" name="add_contact_status" id="addContactYes" value="1" checked>
                <label for="addContactYes">ติดต่อได้</label>
                <input type="radio" name="add_contact_status" id="addContactNo" value="0">
                <label for="addContactNo">ติดต่อไม่ได้</label>
              </div>
            </div>
            <div class="col-md-12">
              <label class="mf-label form-label" for="add_comment_sale">
                <i class="bx bx-comment"></i> หมายเหตุ
              </label>
              <textarea id="add_comment_sale" class="form-control" rows="3" placeholder="รายละเอียด..."></textarea>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-4" id="btnSaveDetail"
              data-tracking-id="{{ $tracking->id }}">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Modal แก้ไขบันทึก --}}
  <div class="modal fade" id="modalEditDetail" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">
        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-edit fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขบันทึกผู้จัดการ</h6>
              <small class="text-white mf-hd-sub">Edit Manager Detail</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_detail_id" value="">
          <div class="row g-4">
            <div class="col-md-5">
              <label class="mf-label form-label"><i class="bx bx-calendar"></i> วันที่ติดต่อ</label>
              <div class="info-pill" id="edit_contact_date_display">—</div>
            </div>
            <div class="col-md-7">
              <label class="mf-label form-label"><i class="bx bx-target-lock"></i> สถานะการตัดสินใจ</label>
              <div class="info-pill" id="edit_decision_display">—</div>
            </div>
            <div class="col-md-12">
              <label class="mf-label form-label" for="editContactYes"><i class="bx bx-phone-call"></i>
                สถานะการติดต่อ</label>
              <div class="yn-group mt-1">
                <input type="radio" name="edit_contact_status" id="editContactYes" value="1" checked>
                <label for="editContactYes">ติดต่อได้</label>
                <input type="radio" name="edit_contact_status" id="editContactNo" value="0">
                <label for="editContactNo">ติดต่อไม่ได้</label>
              </div>
            </div>
            <div class="col-md-12">
              <label class="mf-label form-label" for="edit_comment_sale"><i class="bx bx-comment"></i> หมายเหตุ</label>
              <textarea id="edit_comment_sale" class="form-control" rows="3" placeholder="รายละเอียด..."></textarea>
            </div>

            {{-- ส่วน "ติดตามต่อ" แสดงเฉพาะเมื่อ entry นี้เป็น checkpoint --}}
            <div class="col-md-12" id="editContinueSection" style="display:none;">
              <div class="p-3 rounded" style="background:#fffbeb;border:1px solid #fde68a;">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i class="bx bx-refresh text-warning fs-5"></i>
                  <span class="fw-semibold" style="font-size:.88rem;">ต้องการติดตามลูกค้าคนนี้ต่อไหม?</span>
                </div>
                <label class="mf-label form-label mb-1" for="edit_continue_decision_id">
                  <i class="bx bx-target-lock me-1"></i> สถานะการตัดสินใจ (สำหรับรอบถัดไป)
                </label>
                <select id="edit_continue_decision_id" class="form-select form-select-sm">
                  <option value="">— ไม่ติดตามต่อ —</option>
                  @foreach ($decisions as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                  @endforeach
                </select>
                <small class="text-muted mt-1 d-block" id="editContinueAutoHint" style="display:none;">
                  <i class="bx bx-info-circle me-1"></i> ระบบจะสร้างรายการติดตามอัตโนมัติให้
                </small>
                <div id="editContinueDateWrapper" class="mt-2" style="display:none;">
                  <label class="mf-label form-label mb-1" for="edit_continue_date">
                    <i class="bx bx-calendar me-1"></i> วันที่ติดตาม
                  </label>
                  <input type="date" id="edit_continue_date" class="form-control form-control-sm">
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ยกเลิก
          </button>
          <button type="button" class="btn btn-primary px-4" id="btnSaveEditDetail">
            <i class="bx bx-save me-1"></i> บันทึก
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection
