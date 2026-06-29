<table>
  <thead>
    <tr>
      <th>คำนำหน้า</th>
      <th>ชื่อลูกค้า</th>
      <th>เลขบัตรประชาชน</th>
      <th>เบอร์โทรศัพท์</th>
      <th>ที่อยู่</th>
      <th>บริษัทประกัน</th>
      <th>ชั้นประกัน</th>
      <th>วันที่เริ่มประกัน</th>
      <th>วันที่สิ้นสุดประกัน</th>
      <th>ทุนประกัน</th>
      <th>รหัสประเภทการใช้งานรถ</th>
      <th>ยี่ห้อรถ</th>
      <th>รุ่นรถ</th>
      <th>รุ่นย่อย</th>
      <th>ปีรถ</th>
      <th>สีรถ(ไทย)</th>
      <th>เลขตัวถัง</th>
      <th>เลขเครื่อง</th>
      <th>ราคาขายรถ</th>
      <th>วันที่ส่งมอบรถ</th>
      <th>ติดอุปกรณ์หรือไม่ (ถ้ามี)</th>
      <th>อุปกรณ์ติดตั้งเพิ่มเติม (ถ้ามี)</th>
      <th>ผู้รับผิดชอบ (ถ้ามี)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['prefix'] }}</td>
        <td>{{ $r['customer'] }}</td>
        <td>{{ $r['id_card'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['address'] }}</td>
        <td>{{ $r['insurer'] }}</td>
        <td>{{ $r['insurer_class'] }}</td>
        <td>{{ $r['insure_start'] }}</td>
        <td>{{ $r['insure_end'] }}</td>
        <td>{{ $r['insure_sum'] }}</td>
        <td>{{ $r['usage_code'] }}</td>
        <td>{{ $r['brand'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['subModel'] }}</td>
        <td>{{ $r['year'] }}</td>
        <td>{{ $r['color'] }}</td>
        <td>{{ $r['vin_number'] }}</td>
        <td>{{ $r['engine_number'] }}</td>
        <td>{{ $r['sale_price'] }}</td>
        <td>{{ $r['delivery_date'] }}</td>
        <td>{{ $r['has_accessory'] }}</td>
        <td>{{ $r['extra_accessory'] }}</td>
        <td>{{ $r['responsible'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="23" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
