import './bootstrap';

import Swal from 'sweetalert2';
window.Swal = Swal;

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function initDatePicker(el) {
  if (el._flatpickr) return;
  const val = el.value;
  el.setAttribute('type', 'text');
  el.setAttribute('readonly', 'readonly');
  flatpickr(el, {
    dateFormat: 'Y-m-d',
    allowInput: false,
    defaultDate: val || null,
  });
}

function initAllDatePickers(root) {
  (root || document).querySelectorAll('input[type="date"]').forEach(initDatePicker);
}

document.addEventListener('DOMContentLoaded', function () {
  initAllDatePickers();

  new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeType !== 1) return;
        if (node.matches('input[type="date"]')) {
          initDatePicker(node);
        } else {
          node.querySelectorAll('input[type="date"]').forEach(initDatePicker);
        }
      });
    });
  }).observe(document.body, { childList: true, subtree: true });
});

