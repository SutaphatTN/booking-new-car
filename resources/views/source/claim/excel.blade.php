{{-- รายงานเงินเคลม (Excel) — ลำดับคอลัมน์ตรงกับ MONEY_COLS ใน SourcePlaceClaimExport --}}
@php
  $sumFormB  = 0;
  $sumHalf   = 0;
  $sumActual = 0;
  $sumDiff   = 0;
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>สถานที่</th>
      <th>LAS Number</th>
      <th>สาขา</th>
      <th>วันเริ่มงาน</th>
      <th>วันจบงาน</th>
      <th>Form B</th>
      <th>Form B ({{ $sharePct }}%)</th>
      <th>สถานะ</th>
      <th>ยอดเงินจริงในบัญชี</th>
      <th>Diff</th>
      <th>วันที่ได้รับเงิน</th>
      <th>สถานะการตรวจสอบ</th>
      <th>หมายเหตุ</th>
      <th>ผู้กรอกล่าสุด</th>
      <th>อัปเดตล่าสุด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($places as $i => $p)
      @php
        $c    = $p->claim;
        $diff = $c?->diff();

        $sumFormB  += (float) ($c->form_b ?? 0);
        $sumHalf   += (float) ($c->form_b_half ?? 0);
        $sumActual += (float) ($c->actual_amount ?? 0);
        $sumDiff   += (float) ($diff ?? 0);

        // ผู้กรอกล่าสุด = คนที่แก้ล่าสุด ถ้ายังไม่เคยแก้ก็คือคนที่กรอกครั้งแรก
        $by     = $c?->UserUpdate ? $c->updater : $c?->creator;
        $byName = $by ? ($by->full_name ?: $by->name) : '';
      @endphp
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $p->location }}</td>
        <td>{{ $p->las_number }}</td>
        <td>{{ $p->branch ? ($branchNames[$p->branch] ?? $p->branch) : '' }}</td>
        <td>{{ optional($p->start_date)->format('d/m/Y') }}</td>
        <td>{{ optional($p->end_date)->format('d/m/Y') }}</td>
        <td>{{ $c?->form_b }}</td>
        <td>{{ $c?->form_b_half }}</td>
        <td>{{ $c?->status ? ($statuses[$c->status]['label'] ?? $c->status) : '' }}</td>
        <td>{{ $c?->actual_amount }}</td>
        <td>{{ $diff }}</td>
        <td>{{ optional($c?->received_date)->format('d/m/Y') }}</td>
        <td>{{ $c?->check_status ? ($checkStatuses[$c->check_status]['label'] ?? $c->check_status) : '' }}</td>
        <td>{{ $c?->note }}</td>
        <td>{{ $byName }}</td>
        <td>{{ optional($c?->updated_at)->format('d/m/Y H:i') }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="16" style="text-align:center;">ไม่มีข้อมูล</td>
      </tr>
    @endforelse

    @if ($places->isNotEmpty())
      <tr>
        <td colspan="6" style="text-align:right;">รวม</td>
        <td>{{ $sumFormB }}</td>
        <td>{{ $sumHalf }}</td>
        <td></td>
        <td>{{ $sumActual }}</td>
        <td>{{ $sumDiff }}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    @endif
  </tbody>
</table>
