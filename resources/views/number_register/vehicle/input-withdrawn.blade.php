{{-- ไม่ได้ใช้ --}}
<input type="text" class="form-control text-end money-input input-vehicle" data-sale-id="{{ $SaleID }}"
  data-type="withdrawal" value="{{ number_format($vl?->withdrawal_total ?? 0, 2) }}">
