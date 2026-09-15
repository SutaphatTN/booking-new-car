{{--
  การ์ดไฟล์แนบ 80px — ตัวกลางตัวเดียวของทั้งระบบ (คู่กับ resources/assets/js/file-cards.js)
  รูป = thumbnail (โหลดไม่ขึ้นสลับเป็นการ์ดไฟล์อัตโนมัติ) , ไฟล์อื่น = การ์ดสีตามนามสกุล + ชื่อไฟล์ใต้การ์ด

  ตัวแปร :
    $files      (required) array ของ ['url' => ..., 'name' => ...] หรือ string url ล้วน
    $proxyBase  (optional) base url ของ route proxy — ระบบจะต่อเป็น {base}/{ชื่อไฟล์}?url={url} ให้เอง
                           ถ้าแต่ละ item มีคีย์ 'href' อยู่แล้วจะใช้ค่านั้นแทน
    $deleteUrl  (optional) ใส่แล้วปุ่มกากบาทจะยิง DELETE ทันที (ส่ง index ไปด้วย)
    $keepName   (optional) ชื่อ input hidden ที่เก็บ url ไว้ (เช่น 'keep_files[]') = โหมดลบแบบรอกดบันทึก
    $readonly   (optional) true = ไม่มีปุ่มลบ (ค่าเริ่มต้น false)

  โหมดลบ : ใส่ $deleteUrl = ลบทันที / ใส่ $keepName = เอาออกจากจอรอกดบันทึก / ไม่ใส่ทั้งคู่ = ดูอย่างเดียว
--}}
@php
  $fcProxyBase = $proxyBase ?? null;
  $fcDeleteUrl = $deleteUrl ?? null;
  $fcKeepName = $keepName ?? null;
  $fcReadonly = $readonly ?? false;
  $fcStage = !$fcReadonly && $fcKeepName;
  $fcShowDelete = !$fcReadonly && ($fcDeleteUrl || $fcKeepName);

  // สีต้องตรงกับ EXT_BG ใน resources/assets/js/file-cards.js
  $fcBgMap = [
      'pdf' => '#ef4444',
      'xlsx' => '#16a34a',
      'xls' => '#16a34a',
      'csv' => '#16a34a',
      'doc' => '#2563eb',
      'docx' => '#2563eb',
      'ppt' => '#ea580c',
      'pptx' => '#ea580c',
      'zip' => '#7c3aed',
      'rar' => '#7c3aed',
      '7z' => '#7c3aed',
  ];
  $fcImgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
@endphp

@foreach ($files as $fcItem)
  @php
    $fcUrl = is_array($fcItem) ? $fcItem['url'] ?? '' : $fcItem;
    $fcName = is_array($fcItem) ? $fcItem['name'] ?? null : null;
    $fcExt = $fcName ? strtolower(pathinfo($fcName, PATHINFO_EXTENSION)) : null;
    $fcIsImg = $fcExt && in_array($fcExt, $fcImgExts);
    $fcBg = $fcExt ? $fcBgMap[$fcExt] ?? '#64748b' : '#64748b';
    $fcLabel = $fcExt ? strtoupper($fcExt) : 'FILE';

    $fcHref = is_array($fcItem) && !empty($fcItem['href'])
        ? $fcItem['href']
        : ($fcProxyBase
            ? ($fcName
                ? $fcProxyBase . '/' . rawurlencode($fcName) . '?url=' . urlencode($fcUrl)
                : $fcProxyBase . '?url=' . urlencode($fcUrl))
            : $fcUrl);

    $fcUid = 'fc' . substr(md5($fcUrl . $loop->index), 0, 6);
  @endphp

  <div class="fc-item position-relative d-inline-block m-1" style="width:80px;vertical-align:top;"
    data-index="{{ $loop->index }}" data-url="{{ $fcUrl }}"
    @if ($fcDeleteUrl && !$fcReadonly) data-delete-url="{{ $fcDeleteUrl }}" @endif
    @if ($fcStage) data-stage="1" @endif>

    @if ($fcKeepName)
      {{-- ลบการ์ดทิ้ง = input นี้หายไปด้วย ตอนบันทึก server จึงรู้ว่าเหลือไฟล์ไหนบ้าง --}}
      <input type="hidden" name="{{ $fcKeepName }}" value="{{ $fcUrl }}">
    @endif

    @if ($fcIsImg)
      {{-- รูปที่โหลดไม่ขึ้น (ลิงก์หมดอายุ/สิทธิ์ไม่พอ) สลับไปการ์ดไฟล์แทน จะได้ไม่เหลือกรอบว่าง --}}
      <a href="{{ $fcHref }}" target="_blank" id="img-{{ $fcUid }}" style="display:block;"
        title="{{ $fcName }}">
        <img src="{{ $fcHref }}" class="rounded border"
          style="width:80px;height:80px;object-fit:cover;cursor:pointer;"
          onerror="document.getElementById('img-{{ $fcUid }}').style.display='none';document.getElementById('file-{{ $fcUid }}').style.display='flex';">
      </a>
      <a href="{{ $fcHref }}" target="_blank" id="file-{{ $fcUid }}" class="text-decoration-none"
        style="display:none;width:80px;height:80px;border-radius:.375rem;background:{{ $fcBg }};flex-direction:column;align-items:center;justify-content:center;color:#fff;">
        <i class="bx bx-file" style="font-size:1.8rem;"></i>
        <span class="badge bg-white mt-1"
          style="font-size:.6rem;color:{{ $fcBg }};font-weight:700;">{{ $fcLabel }}</span>
      </a>
    @else
      <a href="{{ $fcHref }}" target="_blank"
        class="d-flex flex-column align-items-center justify-content-center rounded text-white text-decoration-none"
        style="width:80px;height:80px;background:{{ $fcBg }};" title="{{ $fcName }}">
        <i class="bx bx-file" style="font-size:1.8rem;"></i>
        <span class="badge bg-white mt-1"
          style="font-size:.6rem;color:{{ $fcBg }};font-weight:700;">{{ $fcLabel }}</span>
      </a>
    @endif

    @if ($fcName)
      <div class="fc-name text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;"
        title="{{ $fcName }}">{{ $fcName }}</div>
    @endif

    @if ($fcShowDelete)
      <button type="button" class="btn btn-danger fc-delete position-absolute top-0 end-0"
        style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบไฟล์นี้">
        <i class="bx bx-x"></i>
      </button>
    @endif
  </div>
@endforeach
