<table>
  <thead>
    <tr>
      <th>No</th>
      <th>วันที่แจ้งประกัน</th>
      <th>วันที่ FirmCase</th>
      <th>วันที่เงินเข้าบัญชี</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>ชื่อ - นามสกุล ฝ่ายขาย</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>สี</th>
      @if (auth()->user()->brand == 2)
        <th>สีภายใน</th>
      @endif
      <th>ปี</th>
      @if (!in_array(auth()->user()->brand, [2, 3]))
        <th>Option</th>
      @endif
      <th>Vin-Number</th>
      <th>เลขถัง</th>
      <th>ไฟแนนซ์</th>
      <th>ประเภทการขาย</th>
      <th>สาขา</th>
      <th>ราคาทุน</th>
      <th>ราคาขาย</th>
      <th>ส่วนลดราคารถ</th>
      <th>ส่วนลดเงินดาวน์</th>
      <th>ยอดเงินดาวน์</th>
      <th>ยอดบวกหัว</th>
      <th>ราคาขายรวมบวกหัว</th>
      <th>GP</th>
      <th>%GP</th>
      <th>WS</th>
      <th>RI</th>
      <th>ลูกค้าจ่ายเพิ่ม</th>
      <th>แคมเปญ</th>
      <th>แคมเปญ On-Top</th>
      <th>แคมเปญ Other</th>
      <th>แคมเปญ CK</th>
      <th>Total Revenue</th>
      <th>รวมส่วนลด</th>
      <th>คอมขาย</th>
      <th>ต้นทุนรวม</th>
      <th>P/L</th>
      <th>% ดอกเบี้ย</th>
      <th>% คอมดอกเบี้ย</th>
      <th>งวดผ่อน (เดือน)</th>
      <th>งวดผ่อน (ปี)</th>
      <th>ยอดผ่อน/เดือน</th>
      <th>ยอดจัด</th>
      <th>เบี้ยประกัน</th>
      <th>ค่างวด (งวดแรก)</th>
      <th>Com Finance</th>
      <th>Com Finance รับจริง</th>
      <th>Com Extra</th>
      <th>Com Kickback</th>
      <th>Com Subsidy</th>
      <th>ยอดรวมจาก FN</th>
      <th>ยอดรับเข้าบัญชี</th>
      <th>DIFF</th>

      {{-- <th>กำไรขั้นต้น</th>
      <th>% กำไรขั้นต้น</th>
      
      <th>รายได้อื่นๆ</th>
      <th>รวมรายได้อืนๆ</th>
      <th>ค่าใช้จ่ายการขาย</th>
      <th>กำไรสุทธิ</th>
      <th>% กำไรสุทธิ</th> --}}
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
        <td>{{ $p['deliverDate'] }}</td>
        <td>{{ $p['firmDate'] }}</td>
        <td>{{ $p['FNDate'] }}</td>
        <td>{{ $p['customer'] }}</td>
        <td>{{ $p['saleName'] }}</td>
        <td>{{ $p['model'] }}</td>
        <td>{{ $p['subModel'] }}</td>
        <td>{{ $p['color'] }}</td>
        @if (auth()->user()->brand == 2)
          <td>{{ $p['interior_color'] }}</td>
        @endif
        <td>{{ $p['year'] }}</td>
        @if (!in_array(auth()->user()->brand, [2, 3]))
          <td>{{ $p['option'] }}</td>
        @endif
        <td>{{ $p['vin_number'] }}</td>
        <td>{{ $p['engine_number'] }}</td>
        <td>{{ $p['finance'] }}</td>
        <td>{{ $p['type_sale'] }}</td>
        <td>{{ $p['branch_sale'] }}</td>
        <td>{{ $p['cost_price'] }}</td>
        <td>{{ $p['sale_price'] }}</td>
        <td>{{ $p['car_discount'] }}</td>
        <td>{{ $p['down_payDis'] }}</td>
        <td>{{ $p['down_payment'] }}</td>
        <td>{{ $p['makeUp'] }}</td>
        <td>{{ $p['sale_make'] }}</td>
        <td>{{ $p['gp'] }}</td>
        <td>{{ $p['per_gp'] }}</td>
        <td>{{ $p['ws'] }}</td>
        <td>{{ $p['ri'] }}</td>
        <td>{{ $p['acc_extra'] }}</td>
        <td>{{ $p['campaign'] }}</td>
        <td>{{ $p['campaign_top'] }}</td>
        <td>{{ $p['campaign_other'] }}</td>
        <td>{{ $p['campaign_ck'] }}</td>
        <td>{{ $p['total_rev'] }}</td>
        <td>{{ $p['total_discount'] }}</td>
        <td>{{ $p['com_sale'] }}</td>
        <td>{{ $p['total_cost'] }}</td>
        <td>{{ $p['total_pl'] }}</td>
        <td>{{ $p['re_interest'] }}</td>
        <td>{{ $p['re_type_com'] }}</td>
        <td>{{ $p['re_period'] }}</td>
        <td>{{ $p['re_year'] }}</td>
        <td>{{ $p['re_alp'] }}</td>
        <td>{{ $p['balance_fi'] }}</td>
        <td>{{ $p['re_total_alp'] }}</td>
        <td>{{ $p['advance_installment'] }}</td>
        <td>{{ $p['com_fin'] }}</td>
        <td>{{ $p['com_fin_accept'] }}</td>
        <td>{{ $p['com_extra'] }}</td>
        <td>{{ $p['com_kick'] }}</td>
        <td>{{ $p['com_subsidy'] }}</td>
        <td>{{ $p['fn_total'] }}</td>
        <td>{{ $p['actually_received'] }}</td>
        <td>{{ $p['fn_diff'] }}</td>

        {{-- <td>{{ $p['sale_price'] }}</td>
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
      <td>{{ $p['net_percent'] }}</td> --}}
      </tr>
    @empty
      <tr>
        <td colspan="53" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse

    {{-- @if (count($gpPer) > 0)
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
    @endif --}}
  </tbody>
</table>
