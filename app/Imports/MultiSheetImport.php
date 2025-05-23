<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    public $data = [];

    public function sheets(): array
    {
        return [
            'saldo_nl' => new SaldoNLImport($this),
            'kartu_stock' => new KartuStockImport($this),
        ];
    }
}
