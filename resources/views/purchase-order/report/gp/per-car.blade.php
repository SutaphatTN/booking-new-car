<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>ชื่อ - นามสกุล ฝ่ายขาย</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>ราคาขายไม่รวมบวกหัว</th>
      <th>ราคาทุน</th>
      <th>กำไรขั้นต้น</th>
      <th>% กำไรขั้นต้น</th>
      <th>RI</th>
      <th>Support อื่นๆ</th>
      <th>เงินบรรลุเป้ารายคัน</th>
      <th>Com Finance</th>
      <th>Com Extra</th>
      <th>รายได้อื่นๆ</th>
      <th>รวมรายได้อืนๆ</th>
      <th>ค่าใช้จ่ายการขาย</th>
      <th>กำไรสุทธิ</th>
      <th>% กำไรสุทธิ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($gpPer as $p)
    @php
    $row = $loop->iteration + 1;
    $startRow = 2;
    $endRow = count($gpPer) + 1;
    $totalRow = $endRow + 1;
    @endphp
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $p['customer'] }}</td>
      <td>{{ $p['saleName'] }}</td>
      <td>{{ $p['model'] }}</td>
      <td>{{ $p['subModel'] }}</td>

      <td>{{ $p['sale_price'] }}</td>
      <td>{{ $p['cost_price'] }}</td>
      <td>{{ $p['gross_profit'] }}</td>
      <td>{{ $p['gross_percent'] }}</td>

      <td>{{ $p['RI'] }}</td>
      <td></td>
      <td></td>
      <td>{{ $p['com'] }}</td>
      <td>{{ $p['extra'] }}</td>

      <td>{{ $p['other'] }}</td>
      <td>{{ $p['other_income'] }}</td>

      <td>{{ $p['selling_expense'] }}</td>
      <td>{{ $p['net_profit'] }}</td>
      <td>{{ $p['net_percent'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="19" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse

    @if(count($gpPer) > 0)
    <tr>
      <td>Total</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>

      <td>=SUM(F{{ $startRow }}:F{{ $endRow }})</td>
      <td>=SUM(G{{ $startRow }}:G{{ $endRow }})</td>
      <td>=SUM(H{{ $startRow }}:H{{ $endRow }})</td>
      <td>=SUM(I{{ $startRow }}:I{{ $endRow }})</td>
      <td>=SUM(J{{ $startRow }}:J{{ $endRow }})</td>
      <td>=SUM(K{{ $startRow }}:K{{ $endRow }})</td>
      <td>=SUM(L{{ $startRow }}:L{{ $endRow }})</td>
      <td>=SUM(M{{ $startRow }}:M{{ $endRow }})</td>

      <td>=SUM(N{{ $startRow }}:N{{ $endRow }})</td>
      <td>=SUM(O{{ $startRow }}:O{{ $endRow }})</td>

      <td>=SUM(P{{ $startRow }}:P{{ $endRow }})</td>

      <td>=SUM(Q{{ $startRow }}:Q{{ $endRow }})</td>
      <td>=SUM(R{{ $startRow }}:R{{ $endRow }})</td>
      <td>=SUM(S{{ $startRow }}:S{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>