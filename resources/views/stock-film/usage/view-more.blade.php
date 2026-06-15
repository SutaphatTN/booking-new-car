@php
  $isBp        = $usage->type === 'bp';
  $totalSqft   = $usage->items->sum('sqft_used');
  $totalPrice  = $usage->items->sum('price');
  $totalCom    = $usage->items->sum('commission');
  $carText     = $isBp
      ? trim(implode(' ', array_filter([$usage->car_brand, $usage->car_model, $usage->car_year])))
      : ($usage->model?->Name_TH ?? '-');
  $sourceText  = match ($usage->customer_source) {
      'self'      => 'ลูกค้ามาด้วยตัวเอง',
      'insurance' => 'ลูกค้าประกัน',
      default     => '-',
  };
@endphp
<div class="modal fade viewFilmUsage" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-film fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รายละเอียดการใช้ฟิล์ม</h6>
            <small class="text-white mf-hd-sub">
              {{ $isBp ? 'BP — งานซ่อมสี/ตัวถัง' : 'ทั่วไป' }}
              &nbsp;|&nbsp; {{ $usage->order_date?->format('d/m/Y') ?? '-' }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- Section 1 : ข้อมูลทั่วไป --}}
        <div class="mf-section mb-3">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-info-circle"></i>
            </div>
            <span class="mf-section-title">ข้อมูลทั่วไป</span>
            <span class="ms-auto badge {{ $isBp ? 'bg-warning text-dark' : 'bg-info' }}">
              {{ $isBp ? 'BP' : 'ทั่วไป' }}
            </span>
          </div>
          <div class="mf-section-body">
            <div class="row g-2">

              <div class="col-md-4">
                <label class="mf-label form-label">วันที่สั่งงาน</label>
                <input type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $usage->order_date?->format('d/m/Y') ?? '-' }}" disabled>
              </div>

              <div class="col-md-4">
                <label class="mf-label form-label">เลข VIN</label>
                <input type="text" class="form-control form-control-plaintext-mf text-uppercase"
                  value="{{ $usage->vin ?: '-' }}" disabled>
              </div>

              <div class="col-md-4">
                <label class="mf-label form-label">ยี่ห้อฟิล์ม</label>
                <input type="text" class="form-control form-control-plaintext-mf fw-semibold"
                  value="{{ $usage->filmBrand?->name ?? '-' }}" disabled>
              </div>

              <div class="col-md-4">
                <label class="mf-label form-label">ชื่อ-สกุล ลูกค้า</label>
                <input type="text" class="form-control form-control-plaintext-mf"
                  value="{{ $usage->customer_name ?: '-' }}" disabled>
              </div>

              <div class="col-md-4">
                <label class="mf-label form-label">{{ $isBp ? 'ยี่ห้อ / รุ่น / ปี' : 'รุ่นรถ' }}</label>
                <input type="text" class="form-control form-control-plaintext-mf fw-bold text-primary"
                  value="{{ $carText ?: '-' }}" disabled>
              </div>

              @if ($isBp)
                <div class="col-md-4">
                  <label class="mf-label form-label">แหล่งที่มาลูกค้า</label>
                  <input type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $sourceText }}" disabled>
                </div>

                @if ($usage->customer_source === 'insurance')
                  <div class="col-md-4">
                    <label class="mf-label form-label">ประกัน</label>
                    <input type="text" class="form-control form-control-plaintext-mf"
                      value="{{ $usage->insurance_company ?: '-' }}" disabled>
                  </div>
                @endif
              @else
                <div class="col-md-4">
                  <label class="mf-label form-label">ฝ่ายขาย</label>
                  <input type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $usage->sale_person ?: '-' }}" disabled>
                </div>
              @endif

            </div>
          </div>
        </div>

        {{-- Section 2 : รายการฟิล์มที่ใช้ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky">
              <i class="bx bx-layer"></i>
            </div>
            <span class="mf-section-title">รายการฟิล์ม & สต็อกที่ใช้</span>
          </div>
          <div class="mf-section-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0 align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="min-width:150px">ตำแหน่ง</th>
                    <th class="text-center">ความเข้ม</th>
                    <th>Stock No.</th>
                    <th class="text-end">ตร.ฟุต</th>
                    <th class="text-end">ราคาขาย (฿)</th>
                    <th class="text-end">ค่าคอม (฿)</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($usage->items as $item)
                    <tr>
                      <td class="fw-semibold small">{{ $item->position ?: '-' }}</td>
                      <td class="text-center">{{ $item->shade ?: '-' }}</td>
                      <td>
                        @if ($item->stock_no)
                          <span class="badge bg-label-secondary">{{ $item->stock_no }}</span>
                        @else
                          <span class="text-muted">-</span>
                        @endif
                      </td>
                      <td class="text-end">{{ $item->sqft_used !== null ? number_format($item->sqft_used, 2) : '-' }}</td>
                      <td class="text-end">{{ $item->price !== null ? number_format($item->price, 2) : '-' }}</td>
                      <td class="text-end">{{ $item->commission !== null ? number_format($item->commission, 2) : '-' }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-3">ไม่มีรายการ</td>
                    </tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr class="table-light fw-bold">
                    <td colspan="3" class="text-end pe-3">รวมทั้งหมด</td>
                    <td class="text-end">{{ $totalSqft > 0 ? number_format($totalSqft, 2) : '-' }}</td>
                    <td class="text-end">{{ $totalPrice > 0 ? number_format($totalPrice, 2) : '-' }}</td>
                    <td class="text-end">{{ $totalCom > 0 ? number_format($totalCom, 2) : '-' }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i> ปิด
        </button>
      </div>
    </div>
  </div>
</div>
