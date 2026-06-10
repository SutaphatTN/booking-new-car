<div class="modal fade viewFilm" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลสต็อกฟิล์ม</h6>
            <small class="text-white mf-hd-sub">{{ $stock->stock_no }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section 1 : ข้อมูลฟิล์ม --}}
        <div class="mf-section mb-3">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-film"></i>
            </div>
            <span class="mf-section-title">ข้อมูลฟิล์ม</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-2">

              <div class="col-md-6">
                <label for="vm_stock_no" class="mf-label form-label">Stock No.</label>
                <input id="vm_stock_no" type="text" class="form-control form-control-plaintext-mf fw-bold text-primary"
                  value="{{ $stock->stock_no }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="vm_part_no" class="mf-label form-label">Part No.</label>
                <input id="vm_part_no" type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $stock->part_no ?? '-' }}" disabled>
              </div>

              {{-- <div class="col-md-6">
                <label for="vm_brand_group" class="mf-label form-label">กลุ่มแบรนด์</label>
                <input id="vm_brand_group" type="text" class="form-control form-control-plaintext-mf"
                  value="{{ \App\Models\FilmStock::BRAND_GROUPS[$stock->brand_group] ?? $stock->brand_group }}" disabled>
              </div> --}}

              <div class="col-md-7">
                <label for="vm_film_brand" class="mf-label form-label">ยี่ห้อฟิล์ม</label>
                <input id="vm_film_brand" type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $stock->filmBrand?->name ?? '-' }}" disabled>
              </div>

              <div class="col-md-5">
                <label for="vm_shade" class="mf-label form-label">ความเข้ม</label>
                <input id="vm_shade" type="text" class="form-control form-control-plaintext-mf text-center"
                  value="{{ $stock->shade }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="vm_withdrawal_date" class="mf-label form-label">วันที่เบิก</label>
                <input id="vm_withdrawal_date" type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $stock->withdrawal_date?->format('d/m/Y') }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="vm_status" class="mf-label form-label">สถานะ</label>
                @php
                  $remaining = $stock->remaining_qty;
                  $statusText  = $remaining <= 0 ? 'หมด' : ($remaining < 100 ? 'เหลือน้อย' : 'ใช้งาน');
                  $statusClass = $remaining <= 0 ? 'text-secondary' : ($remaining < 100 ? 'text-warning' : 'text-success');
                @endphp
                <input id="vm_status" type="text"
                  class="form-control form-control-plaintext-mf text-center fw-bold {{ $statusClass }}"
                  value="{{ $statusText }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 2 : ปริมาณสต็อก --}}
        <div class="mf-section mb-3">
          <div class="mf-section-hd">
            <div class="mf-section-icon amber">
              <i class="bx bx-ruler"></i>
            </div>
            <span class="mf-section-title">ปริมาณสต็อก (ตร.ฟุต)</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-2">

              <div class="col-md-4">
                <label for="vm_initial_qty" class="mf-label form-label">จำนวนเริ่มต้น</label>
                <input id="vm_initial_qty" type="text" class="form-control form-control-plaintext-mf text-end"
                  value="{{ number_format($stock->initial_qty, 2) }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="vm_used_qty" class="mf-label form-label">ใช้ไปแล้ว</label>
                <input id="vm_used_qty" type="text" class="form-control form-control-plaintext-mf text-end text-danger"
                  value="{{ number_format($stock->used_qty, 2) }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="vm_remaining_qty" class="mf-label form-label">คงเหลือ</label>
                <input id="vm_remaining_qty" type="text" class="form-control form-control-plaintext-mf text-end text-success fw-bold"
                  value="{{ number_format($stock->remaining_qty, 2) }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 3 : ตรวจสอบ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon rose">
              <i class="bx bx-search-alt"></i>
            </div>
            <span class="mf-section-title">ผู้ตรวจสอบ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-2">

              <div class="col-md-6">
                <label for="vm_inspection_date" class="mf-label form-label">วันที่ตรวจสอบ</label>
                <input id="vm_inspection_date" type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $stock->inspection_date?->format('d/m/Y') ?? '-' }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="vm_inspection_qty" class="mf-label form-label">ตรวจสอบคงเหลือ (ตร.ฟุต)</label>
                <input id="vm_inspection_qty" type="text" class="form-control form-control-plaintext-mf text-end"
                  value="{{ $stock->inspection_qty !== null ? number_format($stock->inspection_qty, 2) : '-' }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="vm_inspection_diff" class="mf-label form-label">ยอดส่วนต่าง (ตร.ฟุต)</label>
                @php $diff = $stock->inspection_diff; @endphp
                <input id="vm_inspection_diff" type="text"
                  class="form-control form-control-plaintext-mf text-end fw-bold {{ $diff === null ? '' : ($diff == 0 ? 'text-success' : 'text-danger') }}"
                  value="{{ $diff !== null ? number_format($diff, 2) : '-' }}" disabled>
              </div>

              <div class="col-md-6">
                <label for="vm_inspection_result" class="mf-label form-label">ผลการตรวจนับ</label>
                <input id="vm_inspection_result" type="text"
                  class="form-control form-control-plaintext-mf text-center fw-bold
                    {{ $stock->inspection_result === 'pass' ? 'text-success' : ($stock->inspection_result === 'fail' ? 'text-danger' : '') }}"
                  value="{{ match($stock->inspection_result) { 'pass' => 'ถูกต้อง', 'fail' => 'ไม่ถูกต้อง', default => '-' } }}"
                  disabled>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
