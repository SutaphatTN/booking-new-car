@extends('layouts/contentNavbarLayout')
@section('title', 'Data Forecast Order')

@section('page-script')
<script>
  // กดปุ่ม "คำนวณ" → ส่ง target (ยอดที่อยากสั่งเดือนนี้) ไปคำนวณที่ ForecastController::forecastCalculate
  document.getElementById('btnCalculate').addEventListener('click', function() {

    let target = document.getElementById('target').value;

    // ต้องกรอกยอดก่อน
    if (!target) {
      alert('กรุณากรอกยอด');
      return;
    }

    fetch("{{ route('forecast.calculate') }}", {
        method: "POST",
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          target: target
        })
      })
      .then(res => res.json())
      .then(res => {

        // เช่น target = 0 หรือไม่มียอดขายย้อนหลัง 3 เดือน → backend ส่ง success=false พร้อมข้อความ
        if (!res.success) {
          alert(res.message);
          return;
        }

        // สร้างตารางผลลัพธ์ — คอลัมน์ "ภายใน" (สีภายใน) แสดงเฉพาะ brand 2 (GWM)
        let html = `
        <div class="table-responsive">
        <table class="table table-bordered tbl-table tbl-styled">
            <thead>
                <tr>
                    <th>รุ่น</th>
                    <th>สี</th>
                     ${res.brand == 2 ? '<th>ภายใน</th>' : ''}
                    <th>ขายได้ 3 เดือน</th>
                    <th>จำนวนรถในสต็อค</th>
                    <th>Mix %</th>
                    <th>ควรสั่ง</th>
                </tr>
            </thead>
            <tbody>
        `;

        // หนึ่งแถวต่อหนึ่งรุ่น/สี — forecast_units = จำนวนที่ควรสั่ง (คำนวณจาก backend)
        res.data.forEach(item => {
          html += `
                <tr>
                    <td>${item.subModel}</td>
                    <td>${item.color}</td>
                     ${res.brand == 2 ? `<td>${item.interior_color}</td>` : ''}
                    <td>${item.sold_last_3m}</td>
                    <td>${item.stock_available}</td>
                    <td>${item.mix_percent}%</td>
                    <td><b>${item.forecast_units}</b></td>
                </tr>
            `;
        });

        html += `</tbody></table></div>`;

        document.getElementById('forecastTableArea').innerHTML = html;
      });
  });
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-line-chart fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">Forecast สั่งรถเดือนนี้</div>
          <div class="text-white mf-hd-sub">Forecast Order</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-center gap-3 justify-content-center">
          <div class="d-flex align-items-center gap-2">
            <label class="mb-0">ยอดที่ต้องการสั่งเดือนนี้ :</label>
            <input type="number" id="target" class="form-control form-control-sm" style="width:160px;" placeholder="เช่น 15">
          </div>
          <button class="btn btn-primary btn-sm" id="btnCalculate">
            <i class="bx bx-calculator me-1"></i> คำนวณ
          </button>
        </div>

        <div id="forecastTableArea"></div>

      </div>
    </div>
  </div>
</div>
@endsection
