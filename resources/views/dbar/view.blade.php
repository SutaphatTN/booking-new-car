@extends('layouts/contentNavbarLayout')
@section('title', 'D/Bar')

@section('page-script')
<script>
  // กดปุ่ม "คำนวณ" → ส่ง brand/สาขา + เดือนที่จะสั่ง + เป้า D ไปคำนวณที่ DbarController::calculate
  document.getElementById('btnCalcDbar').addEventListener('click', function () {

    const orderMonth    = document.getElementById('order_month').value;     // เดือนที่จะสั่ง
    const targetCurrent = document.getElementById('target_current').value;  // D

    if (!orderMonth) { alert('กรุณาเลือกเดือนที่จะสั่ง'); return; }
    if (targetCurrent === '') { alert('กรุณากรอกเป้าการขายเดือนปัจจุบัน'); return; }

    fetch("{{ route('dbar.calculate') }}", {
      method: "POST",
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify({ order_month: orderMonth, target_current: targetCurrent })
    })
      .then(res => res.json())
      .then(res => {
        if (!res.success) { alert(res.message || 'คำนวณไม่สำเร็จ'); return; }
        renderSummary(res.summary);
        renderMix(res);
      })
      .catch(() => alert('เกิดข้อผิดพลาดในการคำนวณ'));
  });

  // ── ส่วนที่ 1 : ตารางสรุปยอดที่ต้องสั่ง (A–H) ──
  function renderSummary(s) {
    const row = (label, value, code) =>
      `<tr><td>${label}</td><td class="text-end fw-bold">${value}</td><td class="text-muted small">${code}</td></tr>`;
    const spacer = `<tr><td colspan="3" style="height:10px;border:0;"></td></tr>`;

    document.getElementById('dbarSummary').innerHTML = `
      <div class="table-responsive">
        <table class="table table-bordered tbl-table tbl-styled" style="max-width:700px;">
          <tbody>
            ${row('Stock ทั้งหมด (ทุกสถานะ)', s.stock_all, 'A')}
            ${row('Stock ทั้งหมด (ไม่รวมสถานะส่งแต่ง และผ่านสัญญา)', s.stock_net, 'B')}
            ${row('ส่งมอบแล้ว (ยอด Company เดือน ' + s.current_month + ')', s.delivered_current, 'C')}
            ${spacer}
            ${row('เป้าการขายที่ตั้งในเดือน', s.target_current, 'D')}
            ${row('ยอดขายที่ต้องทำเพิ่มในเดือนปัจจุบัน [D−C]', s.need_more, 'E=D-C')}
            ${row('Stock คงเหลือหลังหักยอดขายในเดือนปัจจุบัน [B−E]', s.stock_after, 'F=B-E')}
            ${spacer}
            ${row('เป้า 100% เดือนถัดไป (' + s.order_month + ') [A−F]', s.target_next, 'G=A-F')}
            ${spacer}
            <tr class="table-warning">
              <td class="fw-bold">รถที่ต้องสั่งเพิ่มเติม [G−F]</td>
              <td class="text-end fw-bold fs-5">${s.to_order}</td>
              <td class="text-muted small">H=G-F</td>
            </tr>
          </tbody>
        </table>
      </div>`;
  }

  // ── ส่วนที่ 2 : ยอดขาย 3 เดือน → เกลี่ยยอดที่ต้องสั่งตามสัดส่วน ──
  function renderMix(res) {
    const isB2 = res.brand == 2;
    let html = `
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4 mb-2">
        <h6 class="mb-0">ยอดขายย้อนหลัง 3 เดือน (${res.summary.mix_range}) → ควรสั่ง</h6>
        <span class="badge bg-label-primary">รวมควรสั่ง ${res.mix_total_order} คัน (= H)</span>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered tbl-table tbl-styled">
          <thead>
            <tr>
              <th>รุ่นหลัก</th>
              <th>รุ่นย่อย</th>
              <th>สี</th>
              ${isB2 ? '<th>สีภายใน</th>' : ''}
              <th class="text-end">ขายได้ 3 เดือน</th>
              <th class="text-end">ควรสั่ง (ตามสัดส่วน)</th>
            </tr>
          </thead>
          <tbody>`;

    if (!res.mix.length) {
      html += `<tr><td colspan="${isB2 ? 6 : 5}" class="text-center text-muted">ไม่มียอดขายในช่วงนี้</td></tr>`;
    } else {
      // ตัวแปรไว้รวมแถวสีของรุ่นหลัก/รุ่นย่อยเดียวกัน (แสดงชื่อครั้งเดียว)
      let prevModel = null, prevSub = null;
      res.mix.forEach(m => {
        const showModel = m.model !== prevModel;
        const showSub   = showModel || m.sub_model !== prevSub;
        html += `
          <tr>
            <td>${showModel ? m.model : ''}</td>
            <td>${showSub ? m.sub_model : ''}</td>
            <td>${m.color}</td>
            ${isB2 ? `<td>${m.interior_color ?? '-'}</td>` : ''}
            <td class="text-end">${m.sold_3m}</td>
            <td class="text-end fw-bold">${m.should_order}</td>
          </tr>`;
        prevModel = m.model;
        prevSub = m.sub_model;
      });
    }

    html += `</tbody></table></div>`;
    document.getElementById('dbarMix').innerHTML = html;
  }
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-bar-chart-alt-2 fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">D/Bar — คำนวณยอดที่ต้องสั่ง</div>
          <div class="text-white mf-hd-sub">Demand / Balance</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-end gap-3 flex-wrap">
          <div>
            <label class="mb-1 small text-muted d-block">เดือนที่จะสั่ง</label>
            <input type="month" id="order_month" class="form-control form-control-sm" style="width:160px;"
              value="{{ now()->addMonthNoOverflow()->format('Y-m') }}">
          </div>
          <div>
            <label class="mb-1 small text-muted d-block">เป้าการขายเดือนปัจจุบัน (D)</label>
            <input type="number" id="target_current" min="0" class="form-control form-control-sm" style="width:170px;"
              placeholder="เช่น 42">
          </div>
          <button class="btn btn-primary btn-sm" id="btnCalcDbar">
            <i class="bx bx-calculator me-1"></i> คำนวณ
          </button>
        </div>

        <div id="dbarSummary" class="mt-3"></div>
        <div id="dbarMix"></div>

      </div>
    </div>
  </div>
</div>
@endsection
