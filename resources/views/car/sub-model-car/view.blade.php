@extends('layouts/contentNavbarLayout')
@section('title', 'Data Sub Model Car')

@section('page-script')
<script>
  window.routeSubModelCreate = "{{ route('model.sub-model.create') }}";
  window.routeSubModelEdit = "{{ route('model.sub-model.edit', ['sub_model_car' => ':id']) }}";
</script>
@vite(['resources/assets/js/car.js'])
@endsection

@section('content')
<div class="viewMoreSubCarModal"></div>
<div class="inputSubCarModal"></div>
<div class="editSubCarModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลรุ่นรถย่อย</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputSubCar">เพิ่ม</button>
          </div>
          <table class="table table-bordered subCarTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>รายละเอียด</th>
                <!-- <th>ปี</th> -->
                <!-- <th>ยอดเงินเกินงบ</th> -->
                <th>สถานะ</th>
                <th width="150px">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection