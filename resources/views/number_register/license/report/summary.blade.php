<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>เบอร์ลูกค้า</th>
      <th>ฝ่ายขาย</th>
      <th>ป้ายแดง</th>
      <th>วันที่ลูกค้าจ่ายเงิน</th>
      <th>หลักฐานการโอนเงิน</th>
      <th>วันที่ส่งมอบ</th>
      <th>ป้ายแดงหน้า</th>
      <th>ป้ายแดงหลัง</th>
      <th>สมุดป้ายแดง</th>
      <th>วันที่คืนเงินลูกค้า</th>
      <th>ยอดคืนเงิน</th>
      <th>ประเภทการคืนเงิน</th>
      <th>สลิปคืนเงินลูกค้า</th>
      <th>การเงิน</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($summary as $s)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $s['customer'] }}</td>
      <td>{{ $s['phone'] }}</td>
      <td>{{ $s['sale_lic'] }}</td>
      <td>{{ $s['red_license'] }}</td>
      <td>{{ $s['pay_date'] }}</td>
      {{-- 2 ช่องนี้เป็น <a href> ต้องปล่อยแท็กออกไป ไม่ escape ไม่งั้นจะได้แท็กดิบ ๆ ในเซลล์แทนลิงก์ --}}
      <td>{!! $s['pay_slip'] !!}</td>
      <td>{{ $s['delivery_date'] }}</td>
      <td>{{ $s['license_front'] }}</td>
      <td>{{ $s['license_back'] }}</td>
      <td>{{ $s['license_book'] }}</td>
      <td>{{ $s['refund_date'] }}</td>
      <td>{{ $s['cost'] }}</td>
      <td>{{ $s['type'] }}</td>
      <td>{!! $s['refund_slip'] !!}</td>
      <td>{{ $s['finance'] }}</td>
      <td>{{ $s['note'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="17" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>
