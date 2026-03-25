<div class="modal fade" id="modalSearchCarOrder" tabindex="-1" aria-labelledby="modalSearchCarOrderLabel" aria-hidden="true"
  role="dialog">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSearchCarOrderLabel">เลือกข้อมูล Car Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered" id="tableSelectCarOrder">
            <thead>
              @php
                $brand = auth()->user()->brand;
              @endphp

              <input type="hidden" id="user_brand" value="{{ auth()->user()->brand }}">
              <tr>
                <th>Car Order ID</th>
                <th>รุ่นรถย่อย</th>
                <th>Vin-number</th>

                @if ($brand == 2)
                  <th>วันที่ Stock</th>
                @else
                  <th>Option</th>
                @endif

                <th>สี</th>
                <th>ปี</th>
                <th>สถานะ Car Order</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
