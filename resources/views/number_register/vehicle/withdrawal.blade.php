<div class="modal fade viewWithdrawal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewWithdrawalLabel">ข้อมูลรอส่งเบิก/เคลียร์</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="nav-align-top ">
          <ul class="nav nav-tabs" id="withdrawalTab" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-withdrawal">
                ส่งเบิก
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-clear">
                ส่งเคลียร์
              </button>
            </li>
          </ul>

          <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="tab-withdrawal">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="checkAll"></th>
                      <th>ชื่อ</th>
                      <th>VIN</th>
                      <th>ตรวจ</th>
                      <th>ช่อง</th>
                      <th>ใบเสร็จ</th>
                      <th>รวมเบิก</th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach ($withdrawalData as $item)
                      <tr>
                        <td>
                          <input type="checkbox" class="checkItem" value="{{ $item->id }}">
                        </td>

                        <td>
                          {{ $item->customer?->prefix?->Name_TH }}
                          {{ $item->customer?->FirstName }}
                          {{ $item->customer?->LastName }}
                        </td>

                        <td>{{ $item->carOrder?->vin_number }}</td>

                        <td>
                          <input type="text" name="withdrawal[{{ $item->id }}][check]"
                            class="form-control calc-input withdrawal-check money-input text-center"
                            data-id="{{ $item->id }}" value="{{ number_format(600, 2) }}">
                        </td>

                        <td>
                          <input type="text" name="withdrawal[{{ $item->id }}][channel]"
                            class="form-control calc-input withdrawal-channel money-input text-center"
                            data-id="{{ $item->id }}" value="{{ number_format(200, 2) }}">
                        </td>

                        <td>
                          <input type="text" name="withdrawal[{{ $item->id }}][receipt]"
                            class="form-control calc-input withdrawal-bill money-input text-center" data-id="{{ $item->id }}">
                        </td>

                        <td>
                          <input type="text" name="withdrawal[{{ $item->id }}][total]"
                            class="form-control withdrawal-total text-center" data-id="{{ $item->id }}" readonly>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <div class="text-end mt-3">
                <button class="btn btn-success btnConfirmWithdrawal">ยืนยันส่งเบิก</button>
              </div>
            </div>

            <div class="tab-pane fade" id="tab-clear">
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="checkAllClear"></th>
                      <th>ชื่อ</th>
                      <th>VIN</th>
                      <th>ตรวจ</th>
                      <th>ช่อง</th>
                      <th>ใบเสร็จ</th>
                      <th>รวมเคลียร์</th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach ($clearData as $item)
                      <tr>
                        <td>
                          <input type="checkbox" class="checkItemClear" value="{{ $item->id }}">
                        </td>

                        <td>
                          {{ $item->customer?->prefix?->Name_TH }}
                          {{ $item->customer?->FirstName }}
                          {{ $item->customer?->LastName }}
                        </td>

                        <td>{{ $item->carOrder?->vin_number }}</td>

                        <td>
                          <input type="text" name="clear[{{ $item->id }}][check]"
                            class="form-control calc-clear receipt-check money-input text-center" data-id="{{ $item->id }}" value="{{ number_format(600, 2) }}">
                        </td>

                        <td>
                          <input type="text" name="clear[{{ $item->id }}][channel]"
                            class="form-control calc-clear receipt-channel money-input text-center" data-id="{{ $item->id }}" value="{{ number_format(200, 2) }}">
                        </td>

                        <td>
                          <input type="text" name="clear[{{ $item->id }}][bill]"
                            class="form-control calc-clear receipt-bill money-input text-center" data-id="{{ $item->id }}">
                        </td>

                        <td>
                          <input type="text" name="clear[{{ $item->id }}][total]"
                            class="form-control receipt-total text-center" data-id="{{ $item->id }}" readonly>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <div class="text-end mt-3">
                <button class="btn btn-primary btnConfirmClear">ยืนยันส่งเคลียร์</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
