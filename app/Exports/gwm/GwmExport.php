<?php

namespace App\Exports\gwm;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GwmExport implements WithMultipleSheets
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function sheets(): array
  {
    return [
      new StockGWMSheet($this->request),
      new BookingGWMSheet($this->request),
    ];
  }
}
