@extends('layouts/contentNavbarLayout')
@section('title', 'Data User')

@section('page-script')
@vite(['resources/assets/js/user.js'])
@endsection

@section('content')
<div class="viewMoreUserModal"></div>
<div class="editUserModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">รายชื่อผู้ใช้งาน</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered userTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
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