@php
  $ro = $canEdit ? '' : 'readonly';
  $roCheck = $canEdit ? '' : 'disabled';
@endphp

<div class="modal fade staffCommissionDetail" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-briefcase-alt-2 fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">{{ $staffUser->name ?? '-' }}</h6>
            <small class="text-white mf-hd-sub">
              {{ $data['label'] }} — ประจำเดือน {{ $monthLabel }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        @unless ($data['active'])
          <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3">
            <i class="bx bx-error-circle"></i>
            <span class="small">
              เดือนนี้ยังไม่เริ่มใช้ระบบค่าคอมฝ่ายสนับสนุน (เริ่ม {{ config('staff_commission.start') }})
            </span>
          </div>
        @endunless

        {{-- ── คอมตามยอดขาย (คิดสดจากจำนวนคัน ไม่ได้เก็บใน DB) ── --}}
        <div class="fw-bold mb-2"><i class="bx bx-car me-1"></i> คอมตามยอดขาย</div>
        <div class="table-responsive mb-3">
          <table class="table table-bordered table-sm align-middle" style="font-size:.85rem;">
            <thead class="table-light">
              <tr class="text-center">
                <th>ฐานที่นับ</th>
                <th style="width:90px;">จำนวนคัน</th>
                <th>เกณฑ์ที่เข้า</th>
                <th class="text-end" style="width:130px;">ยอด</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($data['buckets'] as $b)
                <tr>
                  <td>{{ $b['name'] }}</td>
                  <td class="text-center fw-semibold">{{ number_format($b['count']) }}</td>
                  <td class="{{ $b['amount'] > 0 ? '' : 'text-muted' }}">
                    {{ $b['note'] }}
                    @if (!empty($b['capped']))
                      <span class="badge bg-warning text-dark">ตัดเพดาน</span>
                    @endif
                  </td>
                  <td class="text-end fw-bold {{ $b['amount'] > 0 ? 'text-success' : 'text-muted' }}">
                    {{ number_format($b['amount'], 2) }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">ไม่มีเกณฑ์ที่ตั้งไว้</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">รวมคอมตามยอดขาย</td>
                <td class="text-end">{{ number_format($data['car_total'], 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        {{-- ── รายการที่กรอกเอง (มีเฉพาะบางคน) ── --}}
        @if (!empty($data['extras']))
          <div class="fw-bold mb-2"><i class="bx bx-edit me-1"></i> รายการเพิ่มเติม (กรอกรายเดือน)</div>
        @endif

        <form id="staffCommissionForm">
          <input type="hidden" name="user_id" value="{{ $staffUser->id ?? '' }}">
          <input type="hidden" name="year" value="{{ $year }}">
          <input type="hidden" name="month" value="{{ $month }}">

          @if (!empty($data['extras']))
            <div class="row g-3 align-items-end">
              @foreach ($data['extras'] as $e)
                <div class="col-md-3 col-6">
                  <label class="mf-label form-label" for="extra_{{ $e['key'] }}">
                    <i class="bx bx-plus-circle text-success"></i> {{ $e['label'] }}
                  </label>
                  @if (($e['type'] ?? 'money') === 'bool')
                    <div class="form-check form-switch mt-1">
                      <input class="form-check-input" type="checkbox" id="extra_{{ $e['key'] }}"
                        name="extras[{{ $e['key'] }}]" value="1" data-amount="{{ $e['bonus'] ?? 0 }}"
                        {{ $e['value'] ? 'checked' : '' }} {{ $roCheck }}>
                      <label class="form-check-label small" for="extra_{{ $e['key'] }}">
                        ได้รับ +{{ number_format($e['bonus'] ?? 0, 0) }}
                      </label>
                    </div>
                  @else
                    <input type="text" inputmode="decimal" class="form-control form-control-sm text-end smoney"
                      id="extra_{{ $e['key'] }}" name="extras[{{ $e['key'] }}]"
                      value="{{ number_format($e['value'], 2) }}" {{ $ro }}>
                  @endif
                </div>
              @endforeach

              <div class="col-md-{{ 12 - count($data['extras']) * 3 }} col-12">
                <label for="staff_note" class="mf-label form-label">
                  <i class="bx bx-note text-secondary"></i> หมายเหตุ
                </label>
                <input type="text" class="form-control form-control-sm" id="staff_note" name="note" maxlength="255"
                  value="{{ $data['note'] ?? '' }}" placeholder="ระบุเพิ่มเติมถ้ามี" {{ $ro }}>
              </div>
            </div>
          @endif

          @unless ($canEdit)
            <div class="alert alert-secondary d-flex align-items-center gap-2 py-2 px-3 mt-3 mb-0">
              <i class="bx bx-lock-alt"></i>
              <span class="small">โหมดดูอย่างเดียว — แก้ไขได้เฉพาะ admin, MD และ GM</span>
            </div>
          @endunless

          <div class="d-flex align-items-center justify-content-end gap-3 mt-4 flex-wrap">
            <div class="text-end">
              <div class="text-muted small">ยอดค่าคอมสุทธิ (ตามยอดขาย + รายการเพิ่มเติม)</div>
              <div class="fs-4 fw-bold text-success" id="staffNetDisplay"
                data-car="{{ $data['car_total'] }}">
                {{ number_format($data['total'], 2) }} ฿
              </div>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ปิด
              </button>
              @if ($canEdit && !empty($data['extras']))
                <button type="submit" class="btn btn-success px-4" id="btnSaveStaffCommission">
                  <i class="bx bx-save me-1"></i>บันทึก
                </button>
              @endif
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
