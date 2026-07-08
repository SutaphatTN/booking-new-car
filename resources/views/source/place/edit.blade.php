<div class="modal fade editPlace" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขสถานที่</h6>
            <small class="text-white mf-hd-sub">Edit Place</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        @php $isSettled = $place->isSettled(); @endphp

        @if ($isSettled)
          <div class="alert alert-secondary d-flex align-items-center py-2 mb-3">
            <i class="bx bx-lock-alt me-2 fs-5"></i>
            <div><strong>ปิดยอดแล้ว — ดูข้อมูลได้อย่างเดียว</strong>
              <div class="small text-muted">หากต้องแก้ไข ให้ "เปิดใหม่" ที่หน้าเคลียร์ค่าใช้จ่ายก่อน</div>
            </div>
          </div>
        @endif

        <form action="{{ route('source.place.update', $place->id) }}" method="POST">
          @csrf
          @method('PUT')
          <fieldset {{ $isSettled ? 'disabled' : '' }}>
            @include('source.place._fields', ['place' => $place, 'offlineSources' => $offlineSources])
          </fieldset>

          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>{{ $isSettled ? 'ปิด' : 'ยกเลิก' }}
            </button>
            @unless ($isSettled)
              @if ($place->status === \App\Models\SourcePlace::STATUS_APPROVED)
                <button type="button" class="btn btn-warning px-4 btnOpenTopup" data-id="{{ $place->id }}"
                  {{ $place->pending_extra !== null ? 'disabled' : '' }}>
                  <i class="bx bx-plus-circle me-1"></i>{{ $place->pending_extra !== null ? 'รออนุมัติงบเพิ่ม' : 'ขออนุมัติเพิ่ม' }}
                </button>
              @endif
              <button type="button" class="btn btn-primary px-5 btnUpdatePlace">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
            @endunless
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

{{-- ── Modal ขออนุมัติเพิ่ม (topup) ── --}}
@if ($place->status === \App\Models\SourcePlace::STATUS_APPROVED)
  @php
    $curBudget = (float) ($place->cost ?? 0) + (float) ($place->extra_cost ?? 0);
  @endphp
  <div class="modal fade topupPlace" tabindex="-1" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--input">
        <div class="modal-header mf-header mf-header--input px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon"><i class="bx bx-plus-circle fs-5 text-white"></i></div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">ขออนุมัติเพิ่ม</h6>
              <small class="text-white mf-hd-sub">{{ $place->location }}</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body mf-body">
          <form id="topupForm" action="{{ route('source.place.topup', $place->id) }}" method="POST">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <div class="po-label">งบประมาณปัจจุบัน</div>
                <div class="info-pill text-end">{{ number_format($curBudget, 2) }} ฿</div>
                @if ($place->extra_cost)
                  <small class="text-muted">(ประมาณ {{ number_format($place->cost ?? 0, 2) }} + เพิ่มแล้ว {{ number_format($place->extra_cost, 2) }})</small>
                @endif
              </div>
              <div class="col-md-6">
                <label class="mf-label form-label"><i class="bx bx-money"></i> จำนวนเงินที่ขอเพิ่ม <span class="text-danger">*</span></label>
                <input type="text" name="extra_amount" class="form-control text-end money-input" placeholder="0.00" autocomplete="off" required>
              </div>
              <div class="col-12">
                <label class="mf-label form-label"><i class="bx bx-comment-detail"></i> เหตุผลที่ขอเพิ่ม <span class="text-danger">*</span></label>
                <textarea name="extra_reason" class="form-control" rows="2" placeholder="ระบุเหตุผล..." required></textarea>
              </div>
              <div class="col-md-6">
                <label class="mf-label form-label"><i class="bx bx-calendar"></i> ประจำเดือน <span class="text-danger">*</span></label>
                <input type="month" name="period" class="form-control" value="{{ now()->format('Y-m') }}" required>
              </div>
              <div class="col-md-6">
                <label class="mf-label form-label"><i class="bx bx-user-check"></i> ผู้อนุมัติ (MD) <span class="text-danger">*</span></label>
                <select name="approver_id" class="form-select" required>
                  <option value="">— เลือกผู้อนุมัติ —</option>
                  @foreach ($approvers as $a)
                    <option value="{{ $a->id }}">{{ $a->full_name ?: $a->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2 pt-3">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ยกเลิก</button>
              <button type="button" class="btn btn-primary px-5 btnSubmitTopup" data-id="{{ $place->id }}"><i class="bx bx-send me-1"></i>ส่งคำขอ</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endif
