$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// LIST PAGE — DataTable
$(document).ready(function () {
  if (!$('#serviceCheckTrackingTable').length) return;

  const table = $('#serviceCheckTrackingTable').DataTable({
    ajax: { url: '/service-check-tracking/list' },
    columns: [
      { data: 'No', orderable: false },
      { data: 'FullName' },
      { data: 'model' },
      { data: 'vin' },
      { data: 'delivery' },
      { data: 'last_check' },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        render: function (id) {
          return `
            <div class="d-flex gap-1">
              <a href="/service-check-tracking/${id}" class="btn btn-icon btn-info text-white">
                <i class="bx bx-show"></i>
              </a>
              <button class="btn btn-icon btn-danger text-white btnDeleteTracking" data-id="${id}">
                <i class="bx bx-trash"></i>
              </button>
            </div>`;
        }
      }
    ],
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      search: 'ค้นหา:',
      lengthMenu: 'แสดง _MENU_ รายการ',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' },
      emptyTable: 'ไม่มีข้อมูล',
      zeroRecords: 'ไม่พบข้อมูล'
    }
  });

  $(document).on('click', '.btnDeleteTracking', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'คุณแน่ใจหรือไม่?',
      text: 'ต้องการลบรายการนี้ออกจากการติดตามใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ลบเลย!',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.ajax({
        url: `/service-check-tracking/${id}`,
        type: 'DELETE',
        success: function () {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ลบรายการเรียบร้อยแล้ว',
            timer: 1500,
            showConfirmButton: false
          });
          table.ajax.reload();
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message ?? 'ไม่สามารถลบข้อมูลได้';
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
        }
      });
    });
  });
});

// CREATE PAGE — ค้นหา salecar
$(document).ready(function () {
  if (!$('#salecarSearchInput').length) return;

  function searchSalecar(keyword) {
    if (!keyword.trim()) return;

    $.get('/service-check-tracking/search-salecar', { keyword }, function (data) {
      const $tbody = $('#searchResultBody');
      $tbody.empty();

      if (!data.length) {
        $('#searchResultArea').hide();
        $('#noResultMsg').show();
        return;
      }

      $('#noResultMsg').hide();
      $('#searchResultArea').show();

      data.forEach((row, i) => {
        const alreadyAdded = row.already_added;
        const btnHtml = alreadyAdded
          ? `<span class="badge bg-success px-3 py-2">อยู่ในรายการแล้ว</span>`
          : `<button class="btn btn-sm btn-primary btnAddTracking"
                data-salecar-id="${row.salecar_id}"
                data-customer-id="${row.customer_id}"
                data-car-order-id="${row.car_order_id ?? ''}">
               <i class="bx bx-plus me-1"></i> เพิ่มการติดตาม
             </button>`;

        $tbody.append(`
          <tr>
            <td>${i + 1}</td>
            <td>${row.FullName}</td>
            <td>${row.mobile}</td>
            <td>${row.model}<br><small class="text-muted">${row.color}</small></td>
            <td>${row.vin_number}</td>
            <td>${row.delivery}</td>
            <td>${btnHtml}</td>
          </tr>`);
      });
    }).fail(function () {
      alert('เกิดข้อผิดพลาดในการค้นหา');
    });
  }

  $('#salecarSearchInput').on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      searchSalecar($(this).val());
    }
  });

  $('#btnSearchSalecar').on('click', function () {
    searchSalecar($('#salecarSearchInput').val());
  });

  $(document).on('click', '.btnAddTracking', function () {
    const $btn = $(this);
    const salecarId = $btn.data('salecar-id');
    const customerId = $btn.data('customer-id');
    const carOrderId = $btn.data('car-order-id');

    Swal.fire({
      title: 'ยืนยันการเพิ่มการติดตาม',
      text: 'ต้องการเพิ่มลูกค้าคนนี้เข้าสู่รายการติดตามเช็คระยะใช่หรือไม่?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ยืนยัน',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: '/service-check-tracking',
        type: 'POST',
        data: {
          salecar_id: salecarId,
          customer_id: customerId,
          car_order_id: carOrderId || null
        },
        beforeSend: function () {
          $btn.prop('disabled', true);
        },
        success: function (res) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: res.message,
            timer: 1500,
            showConfirmButton: true
          }).then(() => {
            window.location.href = '/service-check-tracking';
          });
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message ?? 'เกิดข้อผิดพลาด';
          Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg });
          $btn.prop('disabled', false);
        }
      });
    });
  });
});

// VIEW-MORE PAGE — เพิ่มการเช็คระยะ (modal)
$(document).ready(function () {
  $('#btnSaveDetail').on('click', function () {
    const trackingId = $(this).data('tracking-id');
    const checkDate = $('#add_check_date').val();
    const mileage = $('#add_mileage').val();
    const note = $('#add_note').val();

    if (!checkDate) {
      alert('กรุณาระบุวันที่เช็คระยะ');
      return;
    }
    if (!mileage) {
      alert('กรุณาระบุเลขไมล์');
      return;
    }

    $.ajax({
      url: `/service-check-tracking/${trackingId}/detail`,
      type: 'POST',
      data: {
        check_date: checkDate,
        mileage: mileage,
        note: note
      },
      success: function () {
        location.reload();
      },
      error: function () {
        alert('เกิดข้อผิดพลาด ไม่สามารถบันทึกได้');
      }
    });
  });
});
