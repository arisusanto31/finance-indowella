<?php

namespace App\Imports\excel_kartu_stock;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelKartuStockImport implements WithMultipleSheets
{
    public $data = [];

    public function sheets(): array
    {
        return [
            'Kartu Stok' => new _kartu_stock_import($this),
            'Stok Masuk' => new _stock_masuk_import($this),
            'Stok Keluar' => new _stock_keluar_import($this),
        ];
    }
}
