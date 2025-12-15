@extends('layouts/contentNavbarLayout')
@section('title', 'Data Campaign')

@section('page-script')
@vite(['resources/assets/js/campaign.js'])
@endsection

@section('content')
<div class="viewMoreCamModal"></div>
<div class="inputCamModal"></div>
<div class="editCamModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลแคมเปญ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputCam">เพิ่ม</button>
          </div>
          <table class="table table-bordered campaignTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถ</th>
                <th>ชื่อแคมเปญ</th>
                <th>ประเภท</th>
                <th>จำนวนเงิน</th>
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