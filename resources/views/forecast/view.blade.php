@extends('layouts/contentNavbarLayout')
@section('title', 'Data Forecast Order')

@section('page-script')
<script>
  document.getElementById('btnCalculate').addEventListener('click', function() {

    let target = document.getElementById('target').value;

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

        if (!res.success) {
          alert(res.message);
          return;
        }

        let html = `
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รุ่น</th>
                    <th>สี</th>
                    <th>ภายใน</th>
                    <th>ขายได้ 3 เดือน</th>
                    <th>Mix %</th>
                    <th>ควรสั่ง</th>
                </tr>
            </thead>
            <tbody>
        `;

        res.data.forEach(item => {
          html += `
                <tr>
                    <td>${item.subModel}</td>
                    <td>${item.color}</td>
                    <td>${item.interior_color}</td>
                    <td>${item.sold_last_3m}</td>
                    <td>${item.mix_percent}%</td>
                    <td><b>${item.forecast_units}</b></td>
                </tr>
            `;
        });

        html += `</tbody></table>`;

        document.getElementById('forecastTableArea').innerHTML = html;
      });
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <h4>Forecast สั่งรถเดือนนี้</h4>
  </div>

  <div class="card-body">

    <div class="row mb-3 d-flex justify-content-center">
      <div class="col-md-3">
        <label class="mb-2">ยอดที่ต้องการสั่งเดือนนี้</label>
        <input type="number" id="target" class="form-control" placeholder="เช่น 15">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary" id="btnCalculate">
          คำนวณ
        </button>
      </div>
    </div>

    <div id="forecastTableArea"></div>

  </div>
</div>
@endsection