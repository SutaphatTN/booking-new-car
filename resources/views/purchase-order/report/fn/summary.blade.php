<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>Option</th>
      <th>สี</th>
      <th>ปี</th>
      <th>ประกัน ALP</th>
      <th>ไฟแนนซ์</th>
      <th>ดอกเบี้ย</th>
      <th>ประเภทคอม</th>
      <th>จำนวนเดือนที่ผ่อน</th>
      <th>ภาษีหัก ณ ที่จ่าย</th>
      <th>ราคารถ</th>
      <th>Com Fin</th>
      <th>Com Extra</th>
      <th>Com Kickback</th>
      <th>Com Subsidy</th>
      <th>ค่างวดล่วงหน้า</th>
      <th>รวมเงินทั้งหมด</th>
      <th>ยอดที่ได้รับจริง</th>
      <th>Diff</th>
      <th>วันที่ได้รับเงิน</th>
      <th>วันที่เฟิร์มเคส</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($firmFN as $f)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $f['customer'] }}</td>
      <td>{{ $f['model'] }}</td>
      <td>{{ $f['subModel'] }}</td>
      <td>{{ $f['option'] }}</td>
      <td>{{ $f['color'] }}</td>
      <td>{{ $f['year'] }}</td>

      <td>{{ $f['alp'] }}</td>
      <td>{{ $f['name_fi'] }}</td>
      <td>{{ $f['interest'] }}</td>
      <td>{{ $f['total_alp'] }}</td>
      <td>{{ $f['period'] }}</td>
      <td>{{ $f['tax'] }}</td>
      <td>{{ $f['price_sub'] }}</td>
      <td>{{ $f['com_fin'] }}</td>

      <td>{{ $f['com_extra'] }}</td>
      <td>{{ $f['kickback'] }}</td>
      <td>{{ $f['com_subsidy'] }}</td>
      <td>{{ $f['advance_installment'] }}</td>
      <td>{{ $f['total_fi'] }}</td>
      <td>{{ $f['actually_received'] }}</td>
      <td>{{ $f['diff'] }}</td>
      <td>{{ $f['date'] }}</td>
      <td>{{ $f['firm_date'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="24" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>