@extends('layouts/contentNavbarLayout')
@section('title', 'Data Name Campaign')

@section('page-script')
@vite(['resources/assets/js/campaign.js'])
@endsection

@section('content')
<div class="inputCamAppellationModal"></div>
<div class="editCamAppellationModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลชื่อแคมเปญ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputCamAppellation">เพิ่ม</button>
          </div>
          <table class="table table-bordered campaignAppellationTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อแคมเปญ</th>
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