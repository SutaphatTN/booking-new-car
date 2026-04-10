@extends('layouts/contentNavbarLayout')
@section('title', 'ฟอร์มส่งมอบรถ')

@section('content')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <div class="card">
      <h4 class="card-header">ค้นหาลูกค้าเพื่อพิมพ์ฟอร์มส่งมอบรถ</h4>
      <div class="card-body">

        <p class="text-muted mb-3">พิมพ์ชื่อลูกค้า / เลข Vin / เลขถัง เพื่อค้นหา</p>

        <div class="position-relative">
          <input
            type="text"
            id="searchInput"
            class="form-control form-control-lg"
            placeholder="ชื่อลูกค้า / เลข Vin / เลขถัง"
            autocomplete="off"
          >
          <div id="searchSpinner" class="position-absolute top-50 end-0 translate-middle-y pe-3 d-none">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
          </div>
        </div>

        <ul id="resultList" class="list-group mt-2 d-none shadow-sm"></ul>

        <div id="emptyMsg" class="text-center text-muted mt-4 d-none">
          <i class="bx bx-search-alt fs-2 d-block mb-1"></i>
          ไม่พบข้อมูล กรุณาลองค้นหาด้วยคำอื่น
        </div>

      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {
  const input      = $('#searchInput');
  const list       = $('#resultList');
  const spinner    = $('#searchSpinner');
  const emptyMsg   = $('#emptyMsg');
  const searchUrl  = '{{ route("delivery-form.search") }}';
  const formUrl    = '{{ url("delivery-form") }}';

  let timer = null;

  input.on('input', function () {
    const q = $(this).val().trim();

    clearTimeout(timer);
    list.addClass('d-none').empty();
    emptyMsg.addClass('d-none');

    if (q.length < 2) return;

    timer = setTimeout(function () {
      spinner.removeClass('d-none');

      $.get(searchUrl, { q: q }, function (data) {
        spinner.addClass('d-none');

        if (!data.length) {
          emptyMsg.removeClass('d-none');
          return;
        }

        data.forEach(function (item) {
          const li = $('<li>')
            .addClass('list-group-item list-group-item-action d-flex flex-column gap-0 py-2 px-3')
            .css('cursor', 'pointer');

          const topLine = $('<div>').addClass('fw-semibold').text(item.customer_name);
          const subLine = $('<div>').addClass('text-muted small').html(
            item.model + ' / ' + item.color +
            ' &nbsp;|&nbsp; <span class="text-secondary">VIN : ' + item.vin + '</span>'
          );

          li.append(topLine).append(subLine);
          li.on('click', function () {
            window.open(formUrl + '/' + item.id, '_blank');
          });

          list.append(li);
        });

        list.removeClass('d-none');
      }).fail(function () {
        spinner.addClass('d-none');
      });
    }, 300);
  });

  // ปิด dropdown เมื่อคลิกนอก
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#searchInput, #resultList').length) {
      list.addClass('d-none');
    }
  });

  input.on('focus', function () {
    if (list.children().length) {
      list.removeClass('d-none');
    }
  });
});
</script>
@endsection
