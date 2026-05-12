<?php

namespace App\Imports\excel_saldo_awal_stock_jurnal;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelSaldoAwalImport implements WithMultipleSheets,SkipsUnknownSheets
{
    public $data = [];

    public function sheets(): array
    {
        return [
            'saldo_jurnal' => new _saldo_jurnal_awal_import($this),
            'saldo_stock' => new _saldo_stock_awal_import($this),
            'saldo_hutang' => new _saldo_hutang_import($this),
            'inventaris' => new _inventaris_import($this),
            'bdd' => new _bdd_import($this),
            'saldo_stock_in_transit' => new _saldo_stock_in_transit_awal_import($this)

        ];
    }
    public function onUnknownSheet($sheetName)
    {
        
        // optional
        // bisa dikosongi aja
    }
}
