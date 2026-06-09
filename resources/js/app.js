import './bootstrap';

import Swal from 'sweetalert2';
window.Swal = Swal;

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('input[type="date"]').forEach(function (el) {
    const val = el.value;
    el.setAttribute('type', 'text');
    el.setAttribute('readonly', 'readonly');
    flatpickr(el, {
      dateFormat: 'Y-m-d',
      allowInput: false,
      defaultDate: val || null,
    });
  });
});

