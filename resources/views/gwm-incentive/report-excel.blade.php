<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>ยอดขาย (คัน)</th>
      <th>Target (คัน)</th>
      <th>% ทำได้</th>
      <th>ราคารวม (บาท)</th>
      <th>&lt;70% (%)</th>
      <th>70-85% (%)</th>
      <th>85-100% (%)</th>
      <th>100-120% (%)</th>
      <th>≥120% (%)</th>
      <th>Fixed (%)</th>
      <th>Max (%)</th>
      <th>Tier Rate ที่ได้ (%)</th>
      <th>KPI รวม (%)</th>
      <th>รวม % (ก่อน cap)</th>
      <th>Incentive % (หลัง cap)</th>
      <th>ยอด Incentive (บาท)</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($rows as $i => $r)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $r['model_name'] }}</td>
      <td>{{ $r['sub_name'] }}</td>
      <td>{{ $r['count'] }}</td>
      <td>{{ $r['target'] }}</td>
      <td>{{ round($r['achieve_pct'], 2) }}</td>
      <td>{{ round($r['price_total'], 2) }}</td>
      <td>{{ round($r['lt70'], 2) }}</td>
      <td>{{ round($r['gte70_lte85'], 2) }}</td>
      <td>{{ round($r['gt85_lte100'], 2) }}</td>
      <td>{{ round($r['gt100_lte120'], 2) }}</td>
      <td>{{ round($r['gte120'], 2) }}</td>
      <td>{{ round($r['fixed'], 2) }}</td>
      <td>{{ round($r['max_val'], 2) }}</td>
      <td>{{ round($r['tier_rate'], 2) }}</td>
      <td>{{ round($r['kpi_total'], 2) }}</td>
      <td>{{ round($r['total_pct'], 2) }}</td>
      <td>{{ round($r['capped_pct'], 2) }}</td>
      <td>{{ round($r['amount'], 2) }}</td>
    </tr>
    @endforeach

    {{-- Summary row --}}
    @php
      $sumPrice  = $rows->sum('price_total');
      $sumAmount = $rows->sum('amount');
    @endphp
    <tr>
      <td colspan="6">รวมทั้งหมด</td>
      <td>{{ round($sumPrice, 2) }}</td>
      <td colspan="11"></td>
      <td>{{ round($sumAmount, 2) }}</td>
    </tr>
  </tbody>
</table>
