const form = document.getElementById('registerForm');
const actionURL = form.dataset.action;

form.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(form);

  fetch(actionURL, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: 'สร้างบัญชีเรียบร้อยแล้ว',
        confirmButtonText: 'ตกลง'
      }).then(() => {
        form.reset();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: data.message || 'ไม่สามารถบันทึกข้อมูลได้',
        confirmButtonText: 'ตกลง'
      });
    }
  })
  .catch(err => {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'เกิดข้อผิดพลาด',
      text: 'ไม่สามารถบันทึกข้อมูลได้',
      confirmButtonText: 'ตกลง'
    });
  });
});

document.getElementById('cardID').addEventListener('input', function (e) {
  let value = e.target.value.replace(/\D/g, '');

  if (value.length > 1) value = value.slice(0, 1) + '-' + value.slice(1);
  if (value.length > 6) value = value.slice(0, 6) + '-' + value.slice(6);
  if (value.length > 12) value = value.slice(0, 12) + '-' + value.slice(12);
  if (value.length > 15) value = value.slice(0, 15) + '-' + value.slice(15);

  e.target.value = value.slice(0, 17);
});
