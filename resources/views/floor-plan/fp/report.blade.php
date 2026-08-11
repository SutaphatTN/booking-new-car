{{-- รายงาน FP (Excel) — คอลัมน์คงที่ 9 ช่อง ไม่ผูกกับ brand
     แถวที่ยังไม่ปิด FP = ประมาณการ (ทำสีเหลือง + ตัวเอียง ใน FpReportExport) --}}
<table>
  <thead>
    <tr>
      <th>Chassis</th>
      <th>เลขเครื่อง</th>
      <th>Net Amount</th>
      <th>Period_From</th>
      <th>- To</th>
      <th>สถานะ</th>
      <th>No_Day</th>
      <th>Rate</th>
      <th>Amount Due</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['vin'] }}</td>
        <td>{{ $r['engine'] }}</td>
        <td>{{ $r['netAmount'] }}</td>
        <td>{{ $r['billingText'] }}</td>
        {{-- ยังไม่ปิด FP → วันตัดประมาณการ (วันที่ 15 สิ้นงวด) ต่อท้ายด้วย * --}}
        <td>{{ $r['isEstimated'] ? $r['closeText'] . ' *' : $r['closeText'] }}</td>
        <td>
          @if ($r['isClosed'])
            ปิดแล้ว
          @elseif ($r['isEstimated'])
            รอปิด FP (ประมาณการ)
          @else
            รอปิด FP
          @endif
        </td>
        <td>{{ $r['totalDays'] !== null ? $r['totalDays'] : '-' }}</td>
        <td>{{ $r['rate'] !== null ? $r['rate'] : '-' }}</td>
        <td>{{ $r['totalInterest'] !== null ? $r['totalInterest'] : '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="9" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
