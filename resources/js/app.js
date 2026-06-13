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
  // 2. ดูจากไอคอนของการ์ดที่อยู่ใกล้ที่สุด (date-card หรือ po-sub-card)
  const cardIcon = el
    .closest('.date-card, .po-sub-card')
    ?.querySelector('.date-card-icon, .sub-icon');
  if (cardIcon) {
    const m = cardIcon.className.match(/\b(rose|sky|emerald|indigo|amber|purple|orange|pink)\b/);
    if (m) return m[1];
  }
  // 3. ดูจาก section header icon ที่ใกล้ที่สุด
  const sectionIcon = el.closest('.po-section-edit, .po-section, .mf-section, .approval-card')
    ?.querySelector('.po-section-icon, .mf-section-icon, .approval-icon');
  if (sectionIcon) {
    const m = sectionIcon.className.match(/\b(rose|sky|emerald|indigo|amber|purple|orange|pink)\b/);
    if (m) return m[1];
  }
  return 'sky';
}

function initDatePicker(el) {
  if (el._flatpickr) return;
  const val = el.value;
  const color = getDateIconColor(el);
  const isSmall = el.classList.contains('form-control-sm');
  const isRequired = el.hasAttribute('required');
  el.setAttribute('type', 'text');
  el.setAttribute('readonly', 'readonly');
  flatpickr(el, {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd/m/Y',
    altInputClass: isSmall ? 'form-control form-control-sm' : 'form-control',
    allowInput: false,
    defaultDate: val || null,
    onChange: function (_, __, fp) {
      // flatpickr fields can't use native `required` (readonly), so we validate
      // them manually and toggle `is-invalid`; clear it once a date is picked.
      if (fp.altInput) fp.altInput.classList.remove('is-invalid');
    },
    onReady: function (_, __, fp) {
      const alt = fp.altInput;
      if (isRequired) alt.setAttribute('required', 'required');
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

// flatpickr converts date inputs to hidden/readonly fields, which makes the
// browser skip their `required` during constraint validation. Patch the form
// validation methods so required date pickers are enforced everywhere the app
// already calls form.checkValidity()/reportValidity().
const _checkValidity = HTMLFormElement.prototype.checkValidity;
const _reportValidity = HTMLFormElement.prototype.reportValidity;

function firstEmptyRequiredDate(form) {
  return Array.from(form.querySelectorAll('input[required]')).find(
    el => el._flatpickr && !el.value
  );
}

HTMLFormElement.prototype.checkValidity = function () {
  return _checkValidity.call(this) && !firstEmptyRequiredDate(this);
};

HTMLFormElement.prototype.reportValidity = function () {
  // Let the browser report any native invalid fields first.
  if (!_reportValidity.call(this)) return false;
  const empty = firstEmptyRequiredDate(this);
  if (empty) {
    const fp = empty._flatpickr;
    const alt = fp.altInput || empty;
    alt.classList.add('is-invalid');
    alt.scrollIntoView({ behavior: 'smooth', block: 'center' });
    fp.open();
    return false;
  }
  return true;
};

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

