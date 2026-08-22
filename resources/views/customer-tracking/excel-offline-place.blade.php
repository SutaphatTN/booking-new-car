@php
  /*
   | คอลัมน์ที่ "ปิดไว้ก่อน" — comment ไว้ทั้งใน thead และ tbody เผื่อเปิดใช้ทีหลัง :
   |   วันที่กรอก / จำนวนครั้งที่ติดตาม / วันที่ติดต่อล่าสุด / สถานะ / หมายเหตุล่าสุด
   | ค่าทั้งหมดยังถูกคำนวณส่งมาจาก CustomerTrackingOfflinePlaceSheet อยู่แล้ว
   | เปิดกลับ = เอา comment ออกทั้ง 2 ที่ (th + td) แล้วบวกเลขฐาน $colspan ตามจำนวนคอลัมน์ที่เปิด
  */
  $colspan = 11 + ($showInterior ? 1 : 0) + ($showOption ? 1 : 0);
@endphp
<table>
  <thead>
    <tr>
      <th colspan="{{ $colspan }}">{{ $placeType }} &gt; {{ $placeName }}</th>
    </tr>
    <tr>
      <th colspan="{{ $colspan }}">ช่วงวันที่จัดงาน {{ $placeRange }} | เลขที่เอกสาร {{ $placeLasNo }} | ลูกค้าที่กรอกในเดือน {{ $monthLabel }} รวม {{ $rows->count() }} ราย</th>
    </tr>
    <tr>
      <th>No.</th>
      {{-- <th>วันที่กรอก</th> --}}
      <th>ชื่อ - นามสกุล</th>
      <th>เบอร์โทร</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>ปี</th>
      @if ($showInterior)
        <th>สีภายใน</th>
      @endif
      @if ($showOption)
        <th>Option</th>
      @endif
      <th>วันที่ทดลองขับ</th>
      {{-- <th>จำนวนครั้งที่ติดตาม</th> --}}
      {{-- <th>วันที่ติดต่อล่าสุด</th> --}}
      <th>การตัดสินใจล่าสุด</th>
      {{-- <th>สถานะ</th> --}}
      {{-- <th>หมายเหตุล่าสุด</th> --}}
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        {{-- <td>{{ $r['created_at'] }}</td> --}}
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['sub_model'] }}</td>
        <td>{{ $r['color'] }}</td>
        <td>{{ $r['year'] }}</td>
        @if ($showInterior)
          <td>{{ $r['interior_color'] }}</td>
        @endif
        @if ($showOption)
          <td>{{ $r['option'] }}</td>
        @endif
        <td>{{ $r['test_date'] }}</td>
        {{-- <td>{{ $r['follow_count'] }}</td> --}}
        {{-- <td>{{ $r['last_contact'] }}</td> --}}
        <td>{{ $r['last_decision'] }}</td>
        {{-- <td>{{ $r['status'] }}</td> --}}
        {{-- <td>{{ $r['comment'] }}</td> --}}
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colspan }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
