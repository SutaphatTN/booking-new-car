@php
  $accent = $accentColor ?? 'amber';
  $showEdit = $showEdit ?? false;
@endphp
<div class="po-section mb-3" style="border-left:3px solid {{ $accent === 'indigo' ? '#6366f1' : '#d97706' }};">
  <div class="po-section-body">
    <div class="d-flex justify-content-between align-items-start mb-2">
      <div class="d-flex align-items-center gap-2 text-muted" style="font-size:.82rem;">
        <span class="d-flex align-items-center gap-1">
          <i class="bx bx-calendar"></i> {{ $detail->format_contact_date }}
        </span>
        <span class="badge rounded {{ $detail->contact_status ? 'bg-success' : 'bg-danger' }} px-3">
          <i class="bx {{ $detail->contact_status ? 'bx-check' : 'bx-x' }} me-1"></i>
          {{ $detail->contact_status ? 'ติดต่อได้' : 'ติดต่อไม่ได้' }}
        </span>
      </div>
      @if ($showEdit)
        <button type="button" class="btn btn-icon btn-sm btn-warning btnEditDetail" data-id="{{ $detail->id }}"
          data-contact-date="{{ $detail->format_contact_date }}" data-decision="{{ $detail->decision->name ?? '-' }}"
          data-contact-status="{{ $detail->contact_status }}" data-comment="{{ $detail->comment_sale ?? '' }}"
          data-is-checkpoint="{{ $detail->is_checkpoint ? '1' : '0' }}" title="แก้ไข">
          <i class="bx bx-edit-alt"></i>
        </button>
      @endif
    </div>
    <div class="d-flex align-items-center gap-2 mb-1">
      <span class="po-label mb-0"><i class="bx bx-target-lock me-1"></i>สถานะการตัดสินใจ :</span>
      <span class="fw-semibold" style="font-size:.88rem;">{{ $detail->decision->name ?? '-' }}</span>
    </div>
    @if ($detail->comment_sale)
      <div class="mt-2 p-2 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem;color:#374151;">
        {{ $detail->comment_sale }}
      </div>
    @endif
  </div>
</div>
