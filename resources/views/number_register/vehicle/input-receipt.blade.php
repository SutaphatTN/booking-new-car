{{-- ไม่ได้ใช้ --}}
<input type="text" class="form-control text-end money-input input-vehicle" data-sale-id="{{ $SaleID }}"
  data-type="receipt" value="{{ number_format($vl?->receipt_total ?? 0, 2) }}"
  {{ empty($vl?->withdrawal_date) ? 'disabled' : '' }}>
