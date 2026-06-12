$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table campaign claim
let campaignClaimTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.campaignClaimTable')) {
    $('.campaignClaimTable').DataTable().destroy();
  }

  campaignClaimTable = $('.campaignClaimTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
      url: '/campaign/claim/list',
      data: function (d) {
        d.status_filter = $('#claimStatusFilter').val();
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'customer', orderable: false },
      { data: 'saleName', orderable: false },
      { data: 'model', orderable: false },
      { data: 'campaignType', orderable: false },
      { data: 'delivery_date', orderable: false, searchable: false },
      // { data: 'used', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'claim_amount', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'diff', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'received_date', orderable: false, searchable: false },
      // { data: 'status', orderable: false, searchable: false },
      // { data: 'note', orderable: false, searchable: false },
      { data: 'Action', orderable: false, searchable: false }
    ],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      lengthMenu: 'แสดง _MENU_ แถว',
      zeroRecords: 'ไม่พบข้อมูล',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'ไม่มีข้อมูล',
      search: 'ค้นหา:',
      processing: '',
      paginate: {
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });

  //filter : reload on status change
  $(document).on('change', '#claimStatusFilter', function () {
    campaignClaimTable.ajax.reload();
  });

  campaignClaimTable.on('preXhr.dt', function () {
    $('#campaignClaimLoadingOverlay').css('display', 'flex');
  });
  campaignClaimTable.on('xhr.dt', function () {
    $('#campaignClaimLoadingOverlay').css('display', 'none');
  });
});

//css : format number on money input
$(document).on('input', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value === '' || isNaN(value)) {
    this.value = '';
    updateClaimDiff();
    return;
  }
  this.value = parseFloat(value).toLocaleString();
  updateClaimDiff();
});

$(document).on('blur', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value && !isNaN(value)) {
    this.value = parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
});

//calculate diff = ยอดแคมเปญที่ใช้ - ยอดรับเคลม
function updateClaimDiff() {
  const used = parseFloat($('#claim_used').data('raw')) || 0;
  const claim = parseFloat(($('#claim_amount').val() || '').replace(/,/g, '')) || 0;
  const diff = used - claim;

  $('#claim_diff').val(diff.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

// blur focus editClaim
$(document).on('hide.bs.modal', '.editClaim', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : load modal
$(document).on('click', '.btnEditClaim', function () {
  const id = $(this).data('id');

  $.get('/campaign/claim/' + id + '/edit', function (html) {
    $('.editClaimModal').html(html);
    const $modal = $('.editClaim');

    $modal.modal('show');

    setTimeout(updateClaimDiff, 100);

    $modal
      .find('.btnUpdateClaim')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const form = $modal.find('form')[0];
        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,

          beforeSend: function () {
            $modal.modal('hide');

            Swal.fire({
              title: 'กำลังบันทึกข้อมูล...',
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
            });
            $btn.prop('disabled', true);
          },
          success: function (res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            campaignClaimTable.ajax.reload(null, false);
          },
          error: function (xhr) {
            $modal.modal('hide');
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด!',
              text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
            });
          },
          complete: function () {
            $btn.prop('disabled', false);
          }
        });
      });
  });
});
