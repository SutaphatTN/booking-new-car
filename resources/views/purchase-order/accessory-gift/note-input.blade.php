{{-- ช่องหมายเหตุของประดับยนต์ที่ต้องระบุรายละเอียด (ฟิล์ม — ความเข้ม/ตำแหน่งที่ติด)
     class acc-row-note คือจุดที่ JS อ่านค่าไปส่ง server ห้ามเปลี่ยนชื่อ --}}
<div class="acc-note-wrap mt-1">
  <input type="text" class="form-control form-control-sm acc-row-note" maxlength="255"
    value="{{ $note ?? '' }}" placeholder="หมายเหตุฟิล์ม เช่น ความเข้ม 40% รอบคัน / ซันรูฟ 80%"
    @disabled($isHistory ?? false)>
</div>
