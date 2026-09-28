@php
  // รายการรายงานทั้งหมดของหน้าติดตามลูกค้า — เพิ่มรายงานใหม่ = เพิ่มแถวที่นี่ที่เดียว (JS อ่านจาก data-*)
  // input : date = วันเดียว / range = ช่วงวันที่ / month = เดือน
  // saleHidden = ซ่อนจาก role sale (backend กัน 403 ซ้ำอีกชั้นแล้ว)
  $isSale = Auth::user()->role === 'sale';
  $reports = [
    'รายวัน' => [
      ['key' => 'daily', 'url' => '/customer-tracking/export-daily', 'input' => 'date', 'saleHidden' => false,
        'icon' => 'bx-calendar-edit', 'label' => 'การกรอกข้อมูลประจำวัน', 'desc' => 'รายการที่กรอก/ติดตามในวันที่เลือก'],
      ['key' => 'by-date', 'url' => '/customer-tracking/export-by-date', 'input' => 'range', 'saleHidden' => true,
        'icon' => 'bx-user-plus', 'label' => 'เพิ่มลูกค้าประจำวัน', 'desc' => 'ลูกค้าที่เพิ่มเข้าระบบในช่วงวันที่เลือก'],
    ],
    'รายเดือน' => [
      ['key' => 'overdue', 'url' => '/customer-tracking/export-overdue', 'input' => 'month', 'saleHidden' => true,
        'icon' => 'bx-time-five', 'label' => 'เลยกำหนดติดตาม (ผจก.)', 'desc' => 'ลูกค้าที่ยังไม่ได้ติดตามตามกำหนด'],
      ['key' => 'offline-place', 'url' => '/customer-tracking/export-offline-place', 'input' => 'month', 'saleHidden' => true,
        'icon' => 'bx-store', 'label' => 'ลูกค้างาน Offline (แยกสถานที่)', 'desc' => '1 สถานที่ = 1 sheet พร้อมสรุปรวม'],
      ['key' => 'test-drive', 'url' => '/customer-tracking/export-test-drive', 'input' => 'month', 'saleHidden' => false,
        'icon' => 'bx-car', 'label' => 'ทดลองขับ (Test Drive)', 'desc' => $isSale ? 'เฉพาะลูกค้าของคุณ แยก sheet ตามแบรนด์' : 'สรุปต่อเซลล์ + แยก sheet ตามแบรนด์'],
      // เลยกำหนดติดตาม (เซลล์) — ปิดชั่วคราว (ยังไม่เปิดหน้าบ้าน) backend/route พร้อมใช้ เปิดได้โดยเอาคอมเมนต์ออก
      // ['key' => 'overdue-sale', 'url' => '/customer-tracking/export-overdue-sale', 'input' => 'month', 'saleHidden' => false,
      //   'icon' => 'bx-time', 'label' => 'เลยกำหนดติดตาม (เซลล์)', 'desc' => 'sale เห็นเฉพาะของตัวเอง'],
    ],
  ];
  $firstKey = null;
@endphp

<div class="modal fade" id="modalCtReport" tabindex="-1" aria-labelledby="modalCtReportLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow mf-content">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-spreadsheet fs-4 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="modalCtReportLabel">ออกรายงาน</h6>
            <small class="text-white mf-hd-sub">Export Excel</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body px-4">
        @foreach ($reports as $group => $items)
          @php $items = array_filter($items, fn($r) => !($isSale && $r['saleHidden'])); @endphp
          @continue(empty($items))

          <div class="ct-report-group">{{ $group }}</div>
          <div class="d-flex flex-column gap-2 mb-3">
            @foreach ($items as $r)
              @php $firstKey ??= $r['key']; @endphp
              <label class="ct-report-option">
                <input type="radio" name="ctReport" class="form-check-input d-none"
                  value="{{ $r['key'] }}" data-url="{{ $r['url'] }}" data-input="{{ $r['input'] }}"
                  @checked($firstKey === $r['key'])>
                <i class="bx {{ $r['icon'] }} ct-report-icon"></i>
                <span class="d-flex flex-column">
                  <span class="fw-semibold">{{ $r['label'] }}</span>
                  <small class="text-muted">{{ $r['desc'] }}</small>
                </span>
              </label>
            @endforeach
          </div>
        @endforeach

        {{-- ช่องวันที่ — โชว์เฉพาะชุดที่ตรงกับรายงานที่เลือก --}}
        <div class="ct-report-params">
          <div class="ct-report-param" data-for="date">
            <label for="ctReportDate" class="form-label small text-muted mb-1">วันที่</label>
            <input type="date" id="ctReportDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" data-no-icon>
          </div>
          <div class="ct-report-param" data-for="range">
            <label class="form-label small text-muted mb-1">ช่วงวันที่</label>
            <div class="d-flex align-items-center gap-2">
              <input type="date" id="ctReportDateFrom" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" data-no-icon>
              <span class="text-muted small">–</span>
              <input type="date" id="ctReportDateTo" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" data-no-icon>
            </div>
          </div>
          <div class="ct-report-param" data-for="month">
            <label for="ctReportMonth" class="form-label small text-muted mb-1">เดือน</label>
            <input type="month" id="ctReportMonth" class="form-control form-control-sm" value="{{ date('Y-m') }}">
          </div>
        </div>
      </div>

      <div class="modal-footer px-4">
        <button type="button" class="btn btn-label-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-success btn-sm" id="btnCtReportDownload">
          <i class="bx bx-download me-1"></i>ดาวน์โหลด Excel
        </button>
      </div>

    </div>
  </div>
</div>

<style>
  .ct-report-group {
    font-size: .75rem;
    font-weight: 600;
    color: #8592a3;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .4rem;
  }

  .ct-report-option {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem .85rem;
    border: 1px solid #e4e6e8;
    border-radius: .5rem;
    cursor: pointer;
    transition: border-color .15s, background-color .15s;
  }

  .ct-report-option:hover {
    border-color: #a5b4fc;
  }

  .ct-report-option:has(input:checked) {
    border-color: #6366f1;
    background-color: #eef2ff;
  }

  .ct-report-icon {
    font-size: 1.35rem;
    color: #6366f1;
  }

  .ct-report-params {
    padding: .75rem .85rem;
    border-radius: .5rem;
    background-color: #f8f9fa;
  }

  /* โชว์ช่องวันที่ด้วย class (ไม่ใช้ jQuery hide/show — เคยไม่ติดใน modal/tab) */
  .ct-report-param {
    display: none !important;
  }

  .ct-report-param.is-active {
    display: block !important;
  }
</style>
