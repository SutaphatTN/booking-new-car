@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer')
@section('content')

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h3 class="" style="text-align:center;"><b>ข้อมูลรายชื่อลูกค้า</b></h3>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped text-center align-middle" id="customerTable">
            <thead class="table-dark">
              <tr>
                <th class="text-center">No.</th>
                <th class="text-center">รายชื่อ</th>
                <th class="text-center">เลขบัตรประชาชน</th>
                <th class="text-center">เบอร์โทรศัพท์</th>
                <th class="text-center" width="150px">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($customers as $key => $row)
              <tr>
                <td class="text-center">{{ $key+1 }}</td>
                <td class="text-center">{{ $row->Cus_Name }}</td>
                <td class="text-center">{{ $row->Cus_idCard }}</td>
                <td class="text-center">{{ $row->Cus_Tel }}</td>
                <td class="text-center">
                  <button class="btn btn-warning btn-edit" data-id="{{ $row->id }}" data-bs-toggle="modal" data-bs-target="#editModal">แก้ไข</button>
                  <button class="btn btn-danger btn-delete" data-id="{{ $row->id }}">ลบ</button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">แก้ไขข้อมูลลูกค้า</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editForm">
            @csrf
            <input type="hidden" id="edit_id">
            <div class="mb-3">
              <label>ชื่อ-นามสกุล</label>
              <input type="text" id="edit_name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>เลขบัตรประชาชน</label>
              <input type="text" id="edit_idcard" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>เบอร์โทรศัพท์</label>
              <input type="text" id="edit_tel" class="form-control">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary" id="btn-update">บันทึก</button>
        </div>
      </div>
    </div>
  </div>

</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
  $(document).ready(function() {
    $('.btn-delete').click(function() {
      let id = $(this).data('id');
      let button = this;

      Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณต้องการลบรายชื่อลูกค้าคนนี้ใช่หรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/Customers/" + id,
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
              _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
              if (response.status === 'success') {
                Swal.fire(
                  'ลบแล้ว!',
                  response.message,
                  'success'
                );

                $(button).closest('tr').remove();

                $('#customerTable tbody tr').each(function(index) {
                  $(this).find('td:first').text(index + 1);
                });

              } else {
                Swal.fire('ผิดพลาด!', 'ลบไม่สำเร็จ', 'error');
              }
            },
            error: function() {
              Swal.fire('ผิดพลาด!', 'เกิดข้อผิดพลาดในการลบข้อมูล', 'error');
            }
          });
        }
      });
    });
  });
</script>

<script>
  $(document).ready(function() {
    $('.btn-edit').click(function() {
      let id = $(this).data('id');

      $.get("/Customers/" + id, function(data) {
        $('#edit_id').val(data.id);
        $('#edit_name').val(data.Cus_Name);
        $('#edit_idcard').val(data.Cus_idCard);
        $('#edit_tel').val(data.Cus_Tel);

        $('#editModal').modal('show');
      });
    });

    $('#btn-update').click(function() {
      let id = $('#edit_id').val();

      $.ajax({
        url: "/Customers/" + id,
        type: 'PUT',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
          Cus_Name: $('#edit_name').val(),
          Cus_idCard: $('#edit_idcard').val(),
          Cus_Tel: $('#edit_tel').val()
        },
        success: function(response) {
          if (response.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'อัปเดตสำเร็จ!',
              text: response.message,
              timer: 2000,
              showConfirmButton: true
            });

            let row = $('.btn-edit[data-id="' + id + '"]').closest('tr');
            row.find('td:eq(1)').text($('#edit_name').val());
            row.find('td:eq(2)').text($('#edit_idcard').val());
            row.find('td:eq(3)').text($('#edit_tel').val());

            $('#editModal').modal('hide');

          } else {
            Swal.fire({
              icon: 'error',
              title: 'อัปเดตไม่สำเร็จ',
              text: 'กรุณาลองใหม่อีกครั้ง'
            });
          }
        },
        error: function() {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถอัปเดตข้อมูลได้'
          });
        }
      });

    });
  });
</script>
@endsection