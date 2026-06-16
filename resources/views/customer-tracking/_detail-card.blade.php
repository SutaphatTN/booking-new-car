@php
  $accent = $accentColor ?? 'amber';
  $showEdit = $showEdit ?? false;
  $accentClass = $accent === 'indigo' ? 'accent-indigo' : '';
@endphp
<div class="tracking-detail-card {{ $accentClass }} mb-3">
  {{-- Header: date + contact badge + edit button --}}
  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="tracking-detail-date">
        <i class="bx bx-calendar me-1 text-muted"></i>{{ $detail->format_contact_date }}
      </span>
      @if (is_null($detail->contact_status))
        <span class="badge rounded bg-secondary px-3" style="font-size:.78rem;">
          <i class="bx bx-time me-1"></i>ยังไม่ติดต่อ
        </span>
      @else
        <span class="badge rounded {{ $detail->contact_status ? 'bg-success' : 'bg-danger' }} px-3"
          style="font-size:.78rem;">
          <i class="bx {{ $detail->contact_status ? 'bx-check' : 'bx-x' }} me-1"></i>
          {{ $detail->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้' }}
        </span>
      @endif
    </div>
    @if ($showEdit)
      <button type="button" class="btn btn-icon btn-sm btn-warning btnEditDetail flex-shrink-0"
        data-id="{{ $detail->id }}"
        data-contact-date="{{ $detail->format_contact_date }}"
        data-decision="{{ $detail->decision->name ?? '-' }}"
        data-contact-status="{{ $detail->contact_status }}"
        data-comment="{{ $detail->comment_sale ?? '' }}"
        data-is-checkpoint="{{ $detail->is_checkpoint ? '1' : '0' }}"
        title="แก้ไข">
        <i class="bx bx-edit-alt"></i>
      </button>
    @endif
  </div>

  {{-- Decision status: inline on desktop, stacked on mobile --}}
  <div class="detail-decision-row mb-1">
    <span class="detail-decision-label">
      <i class="bx bx-target-lock me-1"></i>สถานะการตัดสินใจ :
    </span>
    <span class="detail-decision-value">{{ $detail->decision->name ?? '-' }}</span>
  </div>

  {{-- Comment --}}
  @if ($detail->comment_sale)
    <div class="tracking-detail-comment">
      <i class="bx bx-comment-detail flex-shrink-0"></i>
      <span>{{ $detail->comment_sale }}</span>
    </div>
  @endif
</div>
