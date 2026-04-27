<?php

namespace App\Exports;

use App\Http\Controllers\ExcelExportController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;

class MultiSheetReportExport implements WithMultipleSheets
{

    use Exportable;
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $month, $year;
    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }
    public function sheets(): array
    {
        $neraca = ExcelExportController::getDataNeraca($this->month, $this->year);
        $nl = ExcelExportController::getDataNL($this->month, $this->year);
        $lr = ExcelExportController::getDataLR($this->month, $this->year);


        $kas = ExcelExportController::getBukuKas($this->month, $this->year);
        $memo = ExcelExportController::getBukuMemo($this->month, $this->year);
        $pembelian = ExcelExportController::getPembelian($this->month, $this->year);
        $penjualan = ExcelExportController::getPenjualan($this->month, $this->year);
        $kartuPiutang = ExcelExportController::getKartuPiutang($this->month, $this->year);
        $kartuHutang = ExcelExportController::getKartuHutang($this->month, $this->year);
        $kartuDPSales = ExcelExportController::getKartuDPSales($this->month, $this->year);
        $kartuInventory = ExcelExportController::getKartuInventory($this->year);
        $kartuBDD = ExcelExportController::getKartuBDD($this->year);
        $kartuStock = ExcelExportController::getKartuStock($this->month, $this->year);
        $kartuBDP = ExcelExportController::getKartuBDP($this->month, $this->year);
        $kartuBahanJadi = ExcelExportController::getKartuBahanJadi($this->month, $this->year);
        $analyze = ExcelExportController::analyze(new Request(
            [
                'month' => $this->month,
                'year' => $this->year,
                'neraca' => $neraca,
                'neraca_lajur' => $nl,
                'laba_rugi' => $lr,
                'kas' => $kas,
                'pembelian' => $pembelian,
                'penjualan' => $penjualan,
                'kartu_piutang' => $kartuPiutang,
                'kartu_hutang' => $kartuHutang,
                'kartu_dpsales' => $kartuDPSales,
                'kartu_inventory' => $kartuInventory,
                'kartu_bdd' => $kartuBDD,
                'kartu_stock' => $kartuStock,
                'kartu_bdp' => $kartuBDP,
                'kartu_bahan_jadi' => $kartuBahanJadi,
            ]
        ));
        return [
            'neraca' => new _NeracaExport($neraca),
            'neraca_lajur' => new _NeracaLajurExport($nl),
            'laba_rugi' => new _LabaRugiExport($lr),
            'kas' => new _BukuBesarKasExport($kas),
            'memo' => new _BukuBesarMemoExport($memo),
            'pembelian' => new _PembelianExport($pembelian),
            'penjualan'  => new _PenjualanExport($penjualan),
            'kartu_piutang' => new _KartuPiutangExport($kartuPiutang),
            'kartu_hutang' => new _KartuHutangExport($kartuHutang),
            'kartu_dpsales' => new _KartuDPSalesExport($kartuDPSales),
            'kartu_inventory' => new _KartuInventoryExport($kartuInventory),
            'kartu_bdd' => new _KartuBDDExport($kartuBDD),
            'kartu_stock' => new _KartuStockExport($kartuStock),
            'kartu_bdp' => new _KartuBDPExport($kartuBDP),
            'kartu_bahan_jadi' => new _KartuBahanJadiExport($kartuBahanJadi),
            'analyze' => new _AnalyzeExport($analyze),

        ];
    }
}
