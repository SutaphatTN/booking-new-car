@extends('layouts/contentNavbarLayout')
@section('title', 'รายละเอียดการติดตามเช็คระยะ')

@section('page-script')
  @vite(['resources/assets/js/service-check-tracking.js'])
@endsection

@section('content')

  {{-- Page Title --}}
  <div class="pur-page-title mb-4 justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <div class="pur-page-icon">
        <i class="bx bx-wrench"></i>
      </div>
      <div>
        <h5 class="pur-page-name">รายละเอียดการติดตามเช็คระยะ</h5>
      </div>
    </div>
  </div>

  <div class="row g-4">

    {{-- ข้อมูลลูกค้าและรถ --}}
    <div class="col-md-5">
      <div class="po-section">
        <div class="po-section-header">
          <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
          <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
        </div>
        <div class="po-section-body">
          @php $c = $tracking->customer; @endphp
          <div class="row g-2">
            <div class="col-12">
              <div class="po-label">ชื่อ - นามสกุล</div>
              <div class="info-val">{{ ($c->prefix->Name_TH ?? '') . ' ' . ($c->FirstName ?? '') . ' ' . ($c->LastName ?? '') }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">เลขบัตรประชาชน</div>
              <div class="info-val">{{ $c->formatted_id_number ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">เบอร์โทรศัพท์</div>
              <div class="info-val">{{ $c->formatted_mobile ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="po-section">
        <div class="po-section-header">
          <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
          <h6 class="po-section-title">ข้อมูลรถ</h6>
        </div>
        <div class="po-section-body">
          @php $s = $tracking->salecar; @endphp
          <div class="row g-2">
            <div class="col-md-6">
              <div class="po-label">รุ่นหลัก</div>
              <div class="info-val">{{ $s?->model?->Name_TH ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">รุ่นย่อย</div>
              <div class="info-val">{{ $s?->subModel?->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">สี</div>
              <div class="info-val">{{ $s?->gwmColor?->name ?? $s?->Color ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">ปี</div>
              <div class="info-val">{{ $s?->Year ?? '-' }}</div>
            </div>
            <div class="col-12">
              <div class="po-label">VIN Number</div>
              <div class="info-val">{{ $tracking->carOrder?->vin_number ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">วันที่ส่งมอบ</div>
              <div class="info-val">{{ $s?->getFormatDeliveryDateAttribute() ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ประวัติการเช็คระยะ --}}
    <div class="col-md-7">
      <div class="po-section">
        <div class="po-section-header d-flex align-items-center justify-content-between w-100">
          <div class="d-flex align-items-center gap-2">
            <div class="po-section-icon amber"><i class="bx bx-notepad"></i></div>
            <h6 class="po-section-title mb-0">ประวัติการเช็คระยะ</h6>
          </div>
          <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalAddDetail">
            <i class="bx bx-plus me-1"></i> เพิ่มการเช็คระยะ
          </button>
        </div>
        <div class="po-section-body">
          @forelse($tracking->details->sortByDesc('check_date') as $detail)
            <div class="tracking-detail-card mb-3">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="tracking-detail-date">
                  <i class="bx bx-calendar me-1"></i>{{ $detail->format_check_date }}
                </span>
                <span class="badge rounded-pill bg-info px-3">
                  <i class="bx bx-tachometer me-1"></i>
                  {{ number_format($detail->mileage) }} กม.
                </span>
              </div>
              @if($detail->note)
                <div class="tracking-detail-comment">
                  <span>{{ $detail->note }}</span>
                </div>
              @endif
            </div>
          @empty
            <div class="text-center text-muted py-4">
              <i class="bx bx-wrench fs-3 d-block mb-1 opacity-50"></i>
              ยังไม่มีประวัติการเช็คระยะ
            </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

  <div class="mt-2 mb-5">
    <a href="{{ route('service-check-tracking.index') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back me-1"></i> กลับ
    </a>
  </div>

  {{-- Modal เพิ่มการเช็คระยะ --}}
  <div class="modal fade" id="modalAddDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">
        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-wrench fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มการเช็คระยะ</h6>
              <small class="text-white mf-hd-sub">Add Service Check</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="po-label" for="add_check_date">วันที่เช็คระยะ</label>
              <input type="date" id="add_check_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="po-label" for="add_mileage">เลขไมล์ (กม.)</label>
              <input type="number" id="add_mileage" class="form-control" min="0" placeholder="เช่น 10000" required>
            </div>
            <div class="col-12">
              <label class="po-label" for="add_note">หมายเหตุ</label>
              <textarea id="add_note" class="form-control" rows="3" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="btnSaveDetail" data-tracking-id="{{ $tracking->id }}">
            <i class="bx bx-save me-1"></i> บันทึก
          </button>
        </div>
      </div>
    </div>
  </div>

@endsection
