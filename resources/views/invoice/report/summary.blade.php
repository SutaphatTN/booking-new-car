<table>
  <thead>
    <tr>
      <th>No</th>
      <th>วันที่วางบิล</th>
      <th>ชื่อร้าน</th>
      <th>รายละเอียด</th>
      <th>เลขถัง</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>ยอดเงิน</th>
      <th>วันที่จ่ายเงิน</th>
      <th>คนวางบิล</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($invoice as $i)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $i['date'] }}</td>
        <td>{{ $i['partner_name'] }}</td>
        <td>{{ $i['detail'] }}</td>
        <td>{{ $i['engine_number'] }}</td>
        <td>{{ $i['customer_name'] }}</td>
        <td>{{ $i['total_price'] }}</td>
        <td>{{ $i['receipt_confirmed_at'] }}</td>
        <td>{{ $i['user_insert'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="9" align="center">
          ไม่มีข้อมูล
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
