$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//list invoiceTable
let invoiceTable;

$(document).ready(function () {
  if (!$('.invoiceTable').length) return;

  let currentFilter = 'pending';

  function initTable(filter) {
    if ($.fn.DataTable.isDataTable('.invoiceTable')) {
      $('.invoiceTable').DataTable().destroy();
    }

    invoiceTable = $('.invoiceTable').DataTable({
      ajax: { url: '/invoice/list', data: { filter: filter } },
      columns: [
        { data: 'No', orderable: false },
        { data: 'customer_name', orderable: false },
        { data: 'partner_name', orderable: false },
        { data: 'detail', orderable: false },
        { data: 'total_price', orderable: false, searchable: false, className: 'text-end' },
        { data: 'date', orderable: false },
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
        paginate: {
          first: '',
          last: '',
          next: 'ถัดไป',
          previous: 'ก่อนหน้า'
        }
      }
    });
  }

  // filter
  $(document).on('change', '#invoiceStatusFilter', function () {
    currentFilter = $(this).val();
    initTable(currentFilter);
  });

  // default filter
  initTable(currentFilter);
});

// save
$(document).on('submit', '#invoiceForm', function (e) {
  e.preventDefault();
  const form = $(this);
  const url = form.attr('action');

  $.ajax({
    url: url,
    method: 'POST',
    data: new FormData(this),
    processData: false,
    contentType: false,
    success: function (res) {
      if (res.success) {
        Swal.fire({
          title: 'สำเร็จ!',
          text: res.message,
          icon: 'success',
          confirmButtonColor: '#6c5ffc',
          confirmButtonText: 'ตกลง'
        }).then(() => {
          window.location.href = '/invoice';
        });
      }
    },
    error: function () {
      Swal.fire('เกิดข้อผิดพลาด', 'กรุณาติดต่อแอดมิน', 'error');
    }
  });
});

// create invoice
let rowIndex = 1;

$(document).ready(function () {
  const btnAdd = document.getElementById('btnAddRow');
  if (btnAdd) {
    const partnerOptions = document.querySelector('#accessoryBody select')?.innerHTML ?? '';

    btnAdd.addEventListener('click', function () {
      const tbody = document.getElementById('accessoryBody');
      const tr = document.createElement('tr');
      tr.className = 'accessory-row';
      tr.innerHTML = `
        <td>
          <select name="accessories[${rowIndex}][acc_partner]" class="form-select" required>
            <option value="">-- เลือกร้าน --</option>
            ${partnerOptions}
          </select>
        </td>
        <td>
          <input type="text" name="accessories[${rowIndex}][detail]" class="form-control" placeholder="รายละเอียด" required>
        </td>
        <td>
          <input type="text" name="accessories[${rowIndex}][cost_price]" class="form-control money-input text-end" placeholder="0.00" required>
        </td>
        <td>
          <input type="text" name="accessories[${rowIndex}][sale_price]" class="form-control money-input text-end" placeholder="0.00" required>
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-danger btnRemoveRow">
            <i class="bx bx-trash"></i>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
      rowIndex++;
    });

    document.getElementById('accessoryBody').addEventListener('click', function (e) {
      if (e.target.closest('.btnRemoveRow')) {
        const rows = document.querySelectorAll('.accessory-row');
        if (rows.length > 1) e.target.closest('tr').remove();
      }
    });
  }
});

// ยืนยันออกใบเสร็จ
let confirmReceiptId = null;

$(document).on('click', '.btn-confirm-receipt', function () {
  confirmReceiptId = $(this).data('id');
  const today = new Date().toISOString().split('T')[0];
  $('#receiptConfirmedDate').val(today);
  $('#confirmReceiptModal').modal('show');
});

$('#btnSubmitConfirmReceipt').on('click', function () {
  const date = $('#receiptConfirmedDate').val();
  if (!date) {
    Swal.fire('กรุณาเลือกวันที่', '', 'warning');
    return;
  }

  $.post('/invoice/' + confirmReceiptId + '/confirm-receipt', { receipt_date: date, _token: $('meta[name="csrf-token"]').attr('content') })
    .done(function () {
      $('#confirmReceiptModal').modal('hide');
      Swal.fire({
        title: 'ยืนยันเรียบร้อย!',
        text: 'ยืนยันการออกใบเสร็จสำเร็จ',
        icon: 'success',
        confirmButtonColor: '#6c5ffc',
        confirmButtonText: 'ตกลง'
      }).then(() => {
        invoiceTable.ajax.reload(null, false);
      });
    })
    .fail(function () {
      Swal.fire('เกิดข้อผิดพลาด', 'กรุณาลองใหม่', 'error');
    });
});

// อนุมัติ
$(document).on('click', '.btn-approve', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'ยืนยันการอนุมัติ?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, อนุมัติ',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.post('/invoice/' + id + '/approve')
        .done(function () {
          invoiceTable.ajax.reload(null, false);
        })
        .fail(function () {
          Swal.fire('เกิดข้อผิดพลาด', 'กรุณาลองใหม่', 'error');
        });
    }
  });
});

//css : format number
$(document).on('input', '.money-input', function () {
  let val = this.value.replace(/,/g, '').replace(/[^0-9.]/g, '');
  const parts = val.split('.');
  parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  this.value = parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
});

$(document).on('blur', '.money-input', function () {
  let val = this.value.replace(/,/g, '');
  if (val && !isNaN(val)) {
    this.value = parseFloat(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
});

$(document).on('focus', '.money-input', function () {
  this.value = this.value.replace(/,/g, '');
});

//format phone
document.getElementById('customer_phone')?.addEventListener('input', function (e) {
  let value = e.target.value.replace(/\D/g, '');

  if (value.length > 10) value = value.substring(0, 10);

  let formatted = '';

  if (value.length > 0) {
    formatted = value.substring(0, 3);
  }
  if (value.length > 3) {
    formatted += '-' + value.substring(3, 7);
  }
  if (value.length > 7) {
    formatted += '-' + value.substring(7, 10);
  }

  e.target.value = formatted;
});

//report
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportInvoice');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});