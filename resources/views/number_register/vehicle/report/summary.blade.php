<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>เลขตัวถัง</th>
      <th>ป้ายแดง</th>
      <th>ป้ายขาว</th>
      <th>จังหวัดป้าย</th>
      <th>วันที่ตั้งเบิก</th>
      <th>ค่าตรวจเบิก</th>
      <th>ค่าช่องเบิก</th>
      <th>ใบเสร็จเบิก</th>
      <th>อื่นๆ เบิก</th>
      <th>รวมเบิก</th>
      <th>วันที่รับป้ายจากขนส่ง</th>
      <th>ค่าตรวจเคลียร์</th>
      <th>ค่าช่องเคลียร์</th>
      <th>ใบเสร็จเคลียร์</th>
      <th>อื่นๆ เคลียร์</th>
      <th>รวมเคลียร์</th>
      {{-- <th>ช่องทางรับป้าย</th> --}}
      {{-- หมายเหตุอยู่ท้ายสุด ไม่ไปคั่นกลุ่มคอลัมน์ตัวเลขที่เอาไว้ซัมยอดในเอ็กเซล --}}
      <th>หมายเหตุ (อื่นๆ เบิก)</th>
      <th>หมายเหตุ (อื่นๆ เคลียร์)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($vehicle as $v)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $v['customer'] }}</td>
      <td>{{ $v['vin'] }}</td>
      <td>{{ $v['red_license'] }}</td>
      <td>{{ $v['w_license'] }}</td>
      <td>{{ $v['province'] }}</td>
      <td>{{ $v['withdrawal_date'] }}</td>
      <td>{{ $v['withdrawal_check'] }}</td>
      <td>{{ $v['withdrawal_channel'] }}</td>
      <td>{{ $v['withdrawal_bill'] }}</td>
      <td>{{ $v['withdrawal_other'] }}</td>
      <td>{{ $v['withdrawal_total'] }}</td>
      <td>{{ $v['backup_clear_date'] }}</td>
      <td>{{ $v['receipt_check'] }}</td>
      <td>{{ $v['receipt_channel'] }}</td>
      <td>{{ $v['receipt_bill'] }}</td>
      <td>{{ $v['receipt_other'] }}</td>
      <td>{{ $v['receipt_total'] }}</td>
      {{-- <td>{{ $v['labe_status'] }}</td> --}}
      <td>{{ $v['withdrawal_other_note'] }}</td>
      <td>{{ $v['receipt_other_note'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="20" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>
