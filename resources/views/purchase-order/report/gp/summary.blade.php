<table>
  <thead>
    <tr>
      <th>ราคาขายไม่รวมบวกหัว</th>
      <th>ราคาทุน</th>
      <th>กำไรขั้นต้น</th>
      <th>% กำไรขั้นต้น</th>
      <th>รายได้อื่นๆ</th>
      <th>ค่าใช้จ่ายการขาย</th>
      <th>กำไรสุทธิ</th>
      <th>% กำไรสุทธิ</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{{ number_format($data['sale_price'],2) }}</td>
      <td>{{ number_format($data['cost_price'],2) }}</td>
      <td>{{ number_format($data['gross_profit'],2) }}</td>
      <td>{{ number_format($data['gross_percent'],2) }}%</td>
      <td>{{ number_format($data['other_income'],2) }}</td>
      <td>{{ number_format($data['selling_expense'],2) }}</td>
      <td>{{ number_format($data['net_profit'],2) }}</td>
      <td>{{ number_format($data['net_percent'],2) }}%</td>
    </tr>
  </tbody>
</table>