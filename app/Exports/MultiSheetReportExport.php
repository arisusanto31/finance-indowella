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
    protected $neraca, $nl, $lr, $kas, $memo, $pembelian, $penjualan, $kartuPiutang, $kartuHutang, $kartuDPSales, $kartuInventory, $kartuBDD, $kartuStock, $kartuBDP, $kartuBahanJadi, $kartuInTransit;
    public function __construct(
        $month,
        $year,
        $neraca,
        $nl,
        $lr,
        $kas,
        $memo,
        $pembelian,
        $penjualan,
        $kartuPiutang,
        $kartuHutang,
        $kartuDPSales,
        $kartuInventory,
        $kartuBDD,
        $kartuStock,
        $kartuBDP,
        $kartuBahanJadi,
        $kartuInTransit
    ) {
        $this->month = $month;
        $this->year = $year;
        $this->neraca = $neraca;
        $this->nl = $nl;
        $this->lr = $lr;
        $this->kas = $kas;
        $this->memo = $memo;
        $this->pembelian = $pembelian;
        $this->penjualan = $penjualan;
        $this->kartuPiutang = $kartuPiutang;
        $this->kartuHutang = $kartuHutang;
        $this->kartuDPSales = $kartuDPSales;
        $this->kartuInventory = $kartuInventory;
        $this->kartuBDD = $kartuBDD;
        $this->kartuStock = $kartuStock;
        $this->kartuBDP = $kartuBDP;
        $this->kartuBahanJadi = $kartuBahanJadi;
        $this->kartuInTransit = $kartuInTransit;
    }
    public function sheets(): array
    {
        // if (!$this->neraca)
        //     $this->neraca = ExcelExportController::getDataNeraca($this->month, $this->year);
        // if (!$this->nl)
        //     $this->nl = ExcelExportController::getDataNL($this->month, $this->year);
        // if (!$this->lr)
        //     $this->lr = ExcelExportController::getDataLR($this->month, $this->year);
        // if (!$this->kas)
        //     $this->kas = ExcelExportController::getBukuKas($this->month, $this->year);
        // if (!$this->memo)
        //     $this->memo = ExcelExportController::getBukuMemo($this->month, $this->year);
        // if (!$this->pembelian)
        //     $this->pembelian = ExcelExportController::getPembelian($this->month, $this->year);
        // if (!$this->penjualan)
        //     $this->penjualan = ExcelExportController::getPenjualan($this->month, $this->year);
        // if (!$this->kartuPiutang)
        //     $this->kartuPiutang = ExcelExportController::getKartuPiutang($this->month, $this->year);
        // if (!$this->kartuHutang)
        //     $this->kartuHutang = ExcelExportController::getKartuHutang($this->month, $this->year);
        // if (!$this->kartuDPSales)
        //     $this->kartuDPSales = ExcelExportController::getKartuDPSales($this->month, $this->year);
        // if (!$this->kartuInventory)
        //     $this->kartuInventory = ExcelExportController::getKartuInventory($this->year);
        // if (!$this->kartuBDD)
        //     $this->kartuBDD = ExcelExportController::getKartuBDD($this->year);
        // if (!$this->kartuStock)
        //     $this->kartuStock = ExcelExportController::getKartuStock($this->month, $this->year);
        // if (!$this->kartuBDP)
        //     $this->kartuBDP = ExcelExportController::getKartuBDP($this->month, $this->year);
        // if (!$this->kartuBahanJadi)
        //     $this->kartuBahanJadi = ExcelExportController::getKartuBahanJadi($this->month, $this->year);
        // if (!$this->kartuInTransit)
        //     $this->kartuInTransit = ExcelExportController::getKartuInTransit($this->month, $this->year);
        $analyze = ExcelExportController::analyze(new Request(
            [
                'month' => $this->month,
                'year' => $this->year,
                'neraca' => $this->neraca,
                'neraca_lajur' => $this->nl,
                'laba_rugi' => $this->lr,
                'kas' => $this->kas,
                'pembelian' => $this->pembelian,
                'penjualan' => $this->penjualan,
                'kartu_piutang' => $this->kartuPiutang,
                'kartu_hutang' => $this->kartuHutang,
                'kartu_dpsales' => $this->kartuDPSales,
                'kartu_inventory' => $this->kartuInventory,
                'kartu_bdd' => $this->kartuBDD,
                'kartu_stock' => $this->kartuStock,
                'kartu_bdp' => $this->kartuBDP,
                'kartu_bahan_jadi' => $this->kartuBahanJadi,
                'kartu_in_transit' => $this->kartuInTransit
            ]
        ));
        return [
            'neraca' => new _NeracaExport($this->neraca),
            'neraca_lajur' => new _NeracaLajurExport($this->nl),
            'laba_rugi' => new _LabaRugiExport($this->lr),
            'kas' => new _BukuBesarKasExport($this->kas),
            'memo' => new _BukuBesarMemoExport($this->memo),
            'pembelian' => new _PembelianExport($this->pembelian),
            'penjualan'  => new _PenjualanExport($this->penjualan),
            'kartu_piutang' => new _KartuPiutangExport($this->kartuPiutang),
            'kartu_hutang' => new _KartuHutangExport($this->kartuHutang),
            'kartu_dpsales' => new _KartuDPSalesExport($this->kartuDPSales),
            'kartu_inventory' => new _KartuInventoryExport($this->kartuInventory),
            'kartu_bdd' => new _KartuBDDExport($this->kartuBDD),
            'kartu_stock' => new _KartuStockExport($this->kartuStock),
            'kartu_bdp' => new _KartuBDPExport($this->kartuBDP),
            'kartu_bahan_jadi' => new _KartuBahanJadiExport($this->kartuBahanJadi),
            'kartu_in_transit' => new _KartuInTransitExport($this->kartuInTransit),
            'analyze' => new _AnalyzeExport($analyze),

        ];
    }
}
