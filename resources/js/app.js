import './bootstrap';

import Swal from 'sweetalert2';
window.Swal = Swal;

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function getDateIconColor(el) {
  // 1. ดูจาก icon ใน label ของ input นี้
  let labelEl = el.id ? document.querySelector(`label[for="${el.id}"]`) : null;
  if (!labelEl) labelEl = el.closest('[class*="col-"]')?.querySelector('label');
  if (labelEl) {
    const m = labelEl.innerHTML.match(/ci-(\w+)/);
    if (m) return m[1];
  }
  // 2. ดูจาก section header icon ที่ใกล้ที่สุด
  const sectionIcon = el.closest('.po-section-edit, .po-section, .mf-section, .approval-card')
    ?.querySelector('.po-section-icon, .mf-section-icon, .approval-icon');
  if (sectionIcon) {
    const m = sectionIcon.className.match(/\b(rose|sky|emerald|indigo|amber|purple|orange)\b/);
    if (m) return m[1];
  }
  return 'sky';
}

function initDatePicker(el) {
  if (el._flatpickr) return;
  const val = el.value;
  const color = getDateIconColor(el);
  const isSmall = el.classList.contains('form-control-sm');
  el.setAttribute('type', 'text');
  el.setAttribute('readonly', 'readonly');
  flatpickr(el, {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    altInputClass: isSmall ? 'form-control form-control-sm' : 'form-control',
    allowInput: false,
    defaultDate: val || null,
    onReady: function (_, __, fp) {
      const alt = fp.altInput;
      if ('noIcon' in el.dataset) {
        if (el.style.width) alt.style.width = el.style.width;
        return;
      }
      if (!alt.closest('.input-group')) {
        const wrapper = document.createElement('div');
        wrapper.className = isSmall ? 'input-group input-group-sm' : 'input-group';
        if (el.style.width) {
          wrapper.style.width = el.style.width;
          alt.style.removeProperty('width');
        }
        const icon = document.createElement('span');
        icon.className = 'input-group-text';
        icon.innerHTML = `<i class="bx bx-calendar-event ci-${color}"></i>`;
        alt.parentNode.insertBefore(wrapper, alt);
        wrapper.appendChild(icon);
        wrapper.appendChild(alt);
      }
    },
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

