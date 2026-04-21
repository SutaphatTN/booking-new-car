@extends('layouts/contentNavbarLayout')
@section('title', 'รายละเอียดการติดตาม')

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')

  {{-- Page Title --}}
  <div class="pur-page-title mb-4 justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <div class="pur-page-icon">
        <i class="bx bx-search-alt"></i>
      </div>
      <div>
        <h5 class="pur-page-name">รายละเอียดการติดตามลูกค้า</h5>
      </div>
    </div>
    <a href="{{ route('purchase-order.create', ['from_tracking' => $tracking->id]) }}" class="btn btn-primary">
      <i class="bx bx-file me-1"></i> สร้างการจอง
    </a>
  </div>

  <div class="row g-4">

    {{-- ข้อมูลหลัก --}}
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
          <div class="po-section-icon indigo"><i class="bx bx-user-pin"></i></div>
          <h6 class="po-section-title">ข้อมูลผู้ขาย</h6>
        </div>
        <div class="po-section-body">
          <div class="row g-2">
            <div class="col-md-6">
              <div class="po-label">ผู้ขาย</div>
              <div class="info-val">{{ $tracking->sale->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">แหล่งที่มา</div>
              <div class="info-val">{{ $tracking->source->name ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="po-section">
        <div class="po-section-header">
          <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
          <h6 class="po-section-title">ข้อมูลรถที่สนใจ</h6>
        </div>
        <div class="po-section-body">
          <div class="row g-2">
            <div class="col-md-6">
              <div class="po-label">รุ่นหลัก</div>
              <div class="info-val">{{ $tracking->model->Name_TH ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">รุ่นย่อย</div>
              <div class="info-val">{{ $tracking->subModel->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">ปี</div>
              <div class="info-val">{{ $tracking->year ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="po-label">สี</div>
              <div class="info-val">{{ $tracking->wuColor->name ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ประวัติการติดตาม --}}
    <div class="col-md-7">
      <div class="po-section">
        <div class="po-section-header d-flex align-items-center justify-content-between w-100">
          <div class="d-flex align-items-center gap-2">
            <div class="po-section-icon amber"><i class="bx bx-notepad"></i></div>
            <h6 class="po-section-title mb-0">ประวัติการติดตาม</h6>
          </div>
          <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalAddDetail">
            <i class="bx bx-plus me-1"></i> เพิ่มการติดตาม
          </button>
        </div>
        <div class="po-section-body">
          @forelse($tracking->details->sortByDesc('contact_date') as $detail)
            <div class="tracking-detail-card mb-3">

              {{-- แถวบน: วันที่ + badge --}}
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="tracking-detail-date">
                  <i class="bx bx-calendar me-1"></i>{{ $detail->format_contact_date }}
                </span>
                <span class="badge rounded-pill {{ $detail->contact_status ? 'bg-success' : 'bg-danger' }} px-3">
                  <i class="bx {{ $detail->contact_status ? 'bx-check' : 'bx-x' }} me-1"></i>
                  {{ $detail->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้' }}
                </span>
              </div>

              {{-- สถานะการตัดสินใจ --}}
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="tracking-detail-label"><i class="bx bx-target-lock me-1"></i>สถานะการตัดสินใจ : </span>
                <span class="tracking-detail-value">{{ $detail->decision->name ?? '-' }}</span>
              </div>

              {{-- หมายเหตุ --}}
              @if($detail->comment_sale)
                <div class="tracking-detail-comment">
                  {{-- <i class="bx bx-comment-detail"></i> --}}
                  <span>{{ $detail->comment_sale }}</span>
                </div>
              @endif

            </div>
          @empty
            <div class="text-center text-muted py-4">
              <i class="bx bx-notepad fs-3 d-block mb-1 opacity-50"></i>
              ยังไม่มีประวัติการติดตาม
            </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

  <div class="mt-2 mb-5">
    <a href="{{ route('customer-tracking.index') }}" class="btn btn-secondary">
      <i class="bx bx-arrow-back me-1"></i> กลับ
    </a>
  </div>

  {{-- Modal เพิ่มการติดตาม --}}
  <div class="modal fade" id="modalAddDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">
        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-notepad fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มการติดตาม</h6>
              <small class="text-white mf-hd-sub">Add Tracking Detail</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="po-label" for="add_contact_date">วันที่ติดต่อ</label>
              <input type="date" id="add_contact_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-7">
              <label class="po-label" for="add_decision_id">สถานะการตัดสินใจ</label>
              <select id="add_decision_id" class="form-select">
                <option value="">— เลือก —</option>
                @foreach($decisions as $d)
                  <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <div class="po-label">สถานะการติดต่อ</div>
              <div class="yn-group mt-1">
                <input type="radio" name="add_contact_status" id="addContactYes" value="1" checked>
                <label for="addContactYes">ติดต่อได้</label>
                <input type="radio" name="add_contact_status" id="addContactNo" value="0">
                <label for="addContactNo">ติดต่อไม่ได้</label>
              </div>
            </div>
            <div class="col-12">
              <label class="po-label" for="add_comment_sale">Comment Sale</label>
              <textarea id="add_comment_sale" class="form-control" rows="3" placeholder="รายละเอียด..."></textarea>
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
