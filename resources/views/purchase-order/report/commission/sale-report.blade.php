<table>
  <thead>
    <tr>
      <th>สาขา</th>
      <th>ชื่อฝ่ายขาย</th>
      <th>รวมจำนวนคัน</th>
      <th>ขายปกติ</th>
      <th>ขายรถ Test Drive</th>
      <th>คอมรายคันรถปกติ</th>
      <th>คอมรายคันรถ Test Drive</th>
      <th>ยอดแบ่งงบเหลือ</th>
      <th>คอมประดับยนต์</th>
      <th>คอมอื่นๆ</th>
      <th>คอมดอกเบี้ย</th>
      <th>คอมไตรมาส</th>
      <th>คอมรถเทิร์น</th>
      <th>ค่าคอมกั๊ก</th>
      <th>คอมรถ Aging</th>
      <th>รวมค่าคอมรับ</th>
      <th>หักงบเกิน</th>
      <th>หักอื่นๆ (หักเงินเดือน/ สาย)</th>
      <th>หัก SSI</th>
      <th>รวมยอดหัก</th>
      <th>คอมสุทธิ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($commission as $c)
    @php
    $row = $loop->iteration + 1;
    $startRow = 2;
    $endRow = count($commission) + 1;
    $totalRow = $endRow + 1;
    @endphp
    <tr>
      <td>{{ $c['branch'] }}</td>
      <td>{{ $c['saleName'] }}</td>
      <td>{{ $c['totalCars'] }}</td>
      <td>{{ $c['retail'] }}</td>
      <td>{{ $c['testDrive'] }}</td>

      <td></td>
      <td></td>
      <td>{{ $c['balanceCampaign'] }}</td>
      <td>{{ $c['accessoryCom'] }}</td>
      <td>{{ $c['specialCom'] }}</td>
      <td>{{ $c['interestCom'] }}</td>
      <td></td>
      <td>{{ $c['turnCarCom'] }}</td>

      <td></td>
      <td></td>

      <td>=SUM(F{{ $row }}:O{{ $row }})</td>

      <td></td>
      <td></td>
      <td>{{ $c['ssi'] }}</td>

      <td>=SUM(Q{{ $row }}:S{{ $row }})</td>
      <td>=P{{ $row }}-T{{ $row }}</td>

    </tr>
    @empty
    <tr>
      <td colspan="21" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse

    @if(count($commission) > 0)
    <tr>
      <td>Total</td>
      <td></td>

      <td>=SUM(C{{ $startRow }}:C{{ $endRow }})</td>
      <td>=SUM(D{{ $startRow }}:D{{ $endRow }})</td>
      <td>=SUM(E{{ $startRow }}:E{{ $endRow }})</td>

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

      <td>=SUM(T{{ $startRow }}:T{{ $endRow }})</td>
      <td>=SUM(U{{ $startRow }}:U{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>