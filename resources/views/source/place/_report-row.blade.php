{{-- แถวเดียวของรายงาน PDF — แยกไฟล์เพราะรายงานวนหลายเซต (เซตละสาขา) ใช้แถวชุดเดียวกัน
     รับ $p (สถานที่) และ $i (ลำดับในเซตนั้น) --}}
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td class="nowrap">{{ $p->las_number ?? '' }}</td>
                    <td class="center nowrap">{{ $fmtDate($p->start_date) }}</td>
                    {{-- จำนวนวันต่อท้ายวันจบงาน (นับรวมวันเริ่มและวันจบ: 1-6 ส.ค. = 6 วัน)
                         ยุบมารวมช่องนี้เพราะแยกเป็นคอลัมน์แล้วตารางล้นกระดาษ --}}
                    <td class="center nowrap">
                        {{ $fmtDate($p->end_date) }}
                        @if ($dayCount($p) !== '')
                            <br><span style="font-size:9px;">({{ $dayCount($p) }} วัน)</span>
                        @endif
                    </td>
                    <td>{{ $p->source->name ?? '' }}</td>
                    <td>{{ $p->location }}</td>
                    @php
                        $eff       = (float) ($p->cost ?? 0) + (float) ($p->extra_cost ?? 0);
                        $actualSum = $p->clears->sum('total');
                        // ข้อมูลเก่าที่ยังไม่ได้แจกแจง → แสดงบรรทัดเดียวแบบเดิม (ประเภทรวมของเดิมไม่ตรงกับประเภทตอนเคลียร์ แจกแจงแล้วจะอ่านยาก)
                        $hasBreakdown = $p->budgetItems->isNotEmpty();
                        $rows = collect();
                        if ($hasBreakdown) {
                            $rows = $p->expenseComparison();
                            if ($p->extra_cost) {
                                $rows->push(['type' => 'งบเพิ่ม', 'estimate' => (float) $p->extra_cost, 'actual' => null, 'extra' => true]);
                            }
                        }
                        // บรรทัด "รวม" ขึ้นเมื่อมีมากกว่า 1 บรรทัด (บรรทัดเดียวยอดก็คือยอดรวมอยู่แล้ว)
                        $showSum = $rows->count() > 1;
                        $sumLine = 'border-top:1px solid #999; font-weight:bold;';
                    @endphp
                    @if (!$hasBreakdown)
                        <td>{{ $p->expense_type ?? '' }}</td>
                        <td class="num">
                            {{ ($p->cost !== null || $p->extra_cost !== null) ? number_format($eff, 2) : '' }}
                            @if ($p->extra_cost)
                                <br><span style="font-size:9px; color:#0a7a3d;">(งบเพิ่ม +{{ number_format($p->extra_cost, 2) }})</span>
                            @endif
                        </td>
                        <td class="num">{{ $p->clears->count() ? number_format($actualSum, 2) : '-' }}</td>
                    @else
                        <td>
                            @foreach ($rows as $r)
                                <div @if (!empty($r['extra'])) style="color:#0a7a3d;" @endif>{{ $r['type'] }}</div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">รวม</div>
                            @endif
                        </td>
                        <td class="num">
                            @foreach ($rows as $r)
                                <div @if (!empty($r['extra'])) style="color:#0a7a3d;" @endif>
                                    {{ $r['estimate'] === null ? '-' : (!empty($r['extra']) ? '+' : '') . number_format($r['estimate'], 2) }}
                                </div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">{{ number_format($eff, 2) }}</div>
                            @endif
                        </td>
                        <td class="num">
                            @foreach ($rows as $r)
                                <div>{{ $r['actual'] === null ? '-' : number_format($r['actual'], 2) }}</div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">{{ $p->clears->count() ? number_format($actualSum, 2) : '-' }}</div>
                            @endif
                        </td>
                    @endif
                    <td class="num">{{ $p->target !== null ? number_format($p->target, 0) : '' }}</td>
                    <td class="num">{{ number_format($p->pp_actual, 0) }}</td>
                    <td class="num">{{ number_format($p->booking_actual, 0) }}</td>
                </tr>
