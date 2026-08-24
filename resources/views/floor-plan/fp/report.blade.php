{{-- รายงาน FP (Excel) — 1 แถว = 1 คัน × 1 งวด
     คันที่คร่อมงวด (16→15) จะแยกเป็นหลายแถว จำนวนวัน/Rate/ดอกเบี้ย = ของงวดนั้นเท่านั้น
     แถวที่ยังไม่ปิด FP = ประมาณการ (ทำสีเหลือง + ตัวเอียง ใน FpReportExport) --}}
<table>
  <thead>
    <tr>
      <th>งวด</th>
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
        <td>{{ $r['periodText'] }}</td>
        <td>{{ $r['vin'] }}</td>
        <td>{{ $r['engine'] }}</td>
        <td>{{ $r['netAmount'] }}</td>
        <td>{{ $r['periodFrom'] }}</td>
        {{-- * เฉพาะงวดที่จบด้วยวันตัดประมาณการ (งวดกลางจบที่วันที่ 15 ตามปกติ ไม่ต้องใส่) --}}
        <td>{{ $r['isEstimateCut'] ? $r['periodTo'] . ' *' : $r['periodTo'] }}</td>
        <td>
          @if ($r['isClosed'])
            ปิดแล้ว
          @elseif ($r['isEstimated'])
            รอปิด FP (ประมาณการ)
          @else
            รอปิด FP
          @endif
        </td>
        <td>{{ $r['segDays'] !== null ? $r['segDays'] : '-' }}</td>
        <td>{{ $r['segRate'] !== null ? $r['segRate'] : '-' }}</td>
        <td>{{ $r['segInterest'] !== null ? $r['segInterest'] : '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="10" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
