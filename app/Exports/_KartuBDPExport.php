<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

use function Laravel\Prompts\form;

class _KartuBDPExport implements FromCollection, WithEvents, WithTitle, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $akhirBaris, $barisHeadings, $headings1, $headings2,$resumeKolom;

    public function __construct($data)
    {   $this->resumeKolom = [];
        $this->data = $data;
        $this->headings1 = [
            'No',
            'Nama Barang',
            'satuan',
            'Saldo Awal',
            "",
            "",
            'Masuk',
            "",
            "",
            'Keluar',
            "",
            "",
            'Saldo Akhir',
            "",
            "",
        ];
        $this->headings2 = ["", "", "", "qty", "rp/unit", "total", "qty", "rp/unit", "total", "qty", "rp/unit", "total", "qty", "rp/unit", "total"];
        $this->barisHeadings = [];
        $this->akhirBaris = [];
    }
    public function collection()
    {
        //
        $fixData = [];
        $baris = 0;
        $globalSaldoAwal=0;
        $globalMutasiMasuk=0;
        $globalMutasiKeluar=0;
        $globalSaldoAkhir=0;
        foreach ($this->data['msg'] as $numProd => $items) {
            $fixData[] = [$numProd];
            $baris++;
            $fixData[] = $this->headings1;
            $baris++;
            $this->barisHeadings[] = $baris;
            $fixData[] = $this->headings2;
            $baris++;
            $this->akhirBaris[] =   (count($items) + $baris+1);

            $totalQtyAwal=0;
            $totalRupiahAwal=0;
            $totalQtyMasuk=0;
            $totalRupiahMasuk=0;
            $totalQtyKeluar=0;
            $totalRupiahKeluar=0;
            $totalQtyAkhir=0;
            $totalRupiahAkhir=0;
            foreach ($items as $i => $item) {

                $mutasiMasuk = data_get($this->data, 'mutasi_masuk.' . $numProd . '.' . $item['id'] . '.qty', 0);
                $rupiahMasuk = data_get($this->data, 'mutasi_masuk.' . $numProd . '.' . $item['id'] . '.total', 0);
                $hargaMasuk = $mutasiMasuk > 0 ? $rupiahMasuk / $mutasiMasuk : 0;
                $mutasiKeluar = data_get($this->data, 'mutasi_keluar.' . $numProd . '.' . $item['id'] . '.qty', 0);
                $rupiahKeluar = data_get($this->data, 'mutasi_keluar.' . $numProd . '.' . $item['id'] . '.total', 0);
                $hargaKeluar = $mutasiKeluar > 0 ? $rupiahKeluar / $mutasiKeluar : 0;

                $totalQtyAwal+= ($item['saldo_qty_awal']/$item['konversi']);
                $totalRupiahAwal+= $item['saldo_rupiah_awal'];
                $totalQtyMasuk+= ($mutasiMasuk/$item['konversi']);
                $totalRupiahMasuk+= $rupiahMasuk;
                $totalQtyKeluar+= ($mutasiKeluar/$item['konversi']);
                $totalRupiahKeluar+= $rupiahKeluar;
                $totalQtyAkhir+= ($item['saldo_qty_akhir']/$item['konversi']);
                $totalRupiahAkhir+= $item['saldo_rupiah_akhir'];
                $globalSaldoAwal+= $item['saldo_rupiah_awal'];
                $globalMutasiMasuk+= $rupiahMasuk;
                $globalMutasiKeluar+= $rupiahKeluar;
                $globalSaldoAkhir+= $item['saldo_rupiah_akhir'];
                $fixData[] = [
                    $i + 1,
                    $item['name'],
                    $item['unit_default'],
                    format_price($item['saldo_qty_awal'] / $item['konversi']),
                    format_price($item['saldo_qty_awal'] > 0 ? ($item['saldo_rupiah_awal'] / $item['saldo_qty_awal']) : 0),
                    format_price($item['saldo_rupiah_awal']),
                    format_price($mutasiMasuk / $item['konversi']),
                    format_price($hargaMasuk),
                    format_price($rupiahMasuk),
                    format_price($mutasiKeluar / $item['konversi']),
                    format_price($hargaKeluar),
                    format_price($rupiahKeluar),
                    format_price($item['saldo_qty_akhir'] / $item['konversi']),
                    format_price($item['saldo_qty_akhir'] > 0 ? ($item['saldo_rupiah_akhir'] / $item['saldo_qty_akhir']) : 0),
                    format_price($item['saldo_rupiah_akhir']),
                ];
                $baris++;
            }
            $fixData[]=[
            "Total",
            "",
            "",
            format_price($totalQtyAwal),
            format_price($totalQtyAwal > 0 ? ($totalRupiahAwal / $totalQtyAwal) : 0),
            format_price($totalRupiahAwal),
            format_price($totalQtyMasuk),
            format_price($totalQtyMasuk > 0 ? ($totalRupiahMasuk / $totalQtyMasuk) : 0),
            format_price($totalRupiahMasuk),
            format_price($totalQtyKeluar),
            format_price($totalQtyKeluar > 0 ? ($totalRupiahKeluar / $totalQtyKeluar) : 0),
            format_price($totalRupiahKeluar),
            format_price($totalQtyAkhir),
            format_price($totalQtyAkhir > 0 ? ($totalRupiahAkhir / $totalQtyAkhir) : 0),
            format_price($totalRupiahAkhir),
            ];
            $baris++;
            $fixData[] = [""];
            $baris++;
        }

        
        $fixData[]=[];
        $fixData[]=['Resume Keseluruhan'];
        $baris+=2;
        $this->resumeKolom = ['start' => 'A' . $baris, 'end' => 'B' . ($baris+4),'startNum'=> 'B'.$baris];
        $fixData[]=[
            "Total Saldo Awal", format_price($globalSaldoAwal)
        ];
        $fixData[]=[
            "Total Mutasi Masuk", format_price($globalMutasiMasuk)
        ];
        $fixData[]=[
            "Total Mutasi Keluar", format_price($globalMutasiKeluar)
        ];
        $fixData[]= [
            "Total Saldo Akhir", format_price($globalSaldoAkhir)
        ];
        return collect($fixData);
    }




    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                info('ini baris heading');
                info(json_encode($this->barisHeadings));
                $sheet->freezePane('D1');
                foreach ($this->barisHeadings as $row => $b) {
                    $sheet->mergeCells('A' . $b . ':A' . ($b + 1));
                    $sheet->mergeCells('B' . $b . ':B' . ($b + 1));
                    $sheet->mergeCells('C' . $b . ':C' . ($b + 1));
                    $sheet->mergeCells('D' . $b . ':F' . $b);
                    $sheet->mergeCells('G' . $b . ':I' . $b);
                    $sheet->mergeCells('J' . $b . ':L' . $b);
                    $sheet->mergeCells('M' . $b . ':O' . $b);
                    $sheet->mergeCells('A' . ($b - 1) . ':D' . ($b - 1));

                    $sheet->getStyle('A' . ($b - 1) . ':O' . ($b + 1))->getFont()->setBold(true);
                    $sheet->getStyle('A' . $b . ':O' . ($b + 1))->getAlignment()->setHorizontal('center');
                    $sheet->getStyle('A' . $b . ':O' . ($b + 1))->getAlignment()->setVertical('center');
                    $sheet->getStyle('D' . ($b + 2) . ':O' . $this->akhirBaris[$row])->getAlignment()->setHorizontal('right');
                    $sheet->getStyle('A' . $this->akhirBaris[$row] . ':O' . $this->akhirBaris[$row])->getFont()->setBold(true);
                  
                    $range = 'A' . $b . ':O' . $this->akhirBaris[$row];
                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
                }

                $sheet->getStyle($this->resumeKolom['start'] . ':' . $this->resumeKolom['end'])->getFont()->setBold(true);
                $sheet->getStyle($this->resumeKolom['start'] . ':' . $this->resumeKolom['end'])->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle($this->resumeKolom['startNum'] . ':' . $this->resumeKolom['end'])->getAlignment()->setHorizontal('right');
                
            },
        ];
    }

    public function title(): string
    {
        return 'K.BDP ' . $this->data['year'] . '-' . $this->data['month'];
    }
}
