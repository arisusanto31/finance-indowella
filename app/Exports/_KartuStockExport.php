<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

use function Laravel\Prompts\form;

class _KartuStockExport implements FromCollection, WithTitle, WithEvents, ShouldAutoSize,WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $kotakRange, $headings, $mergeFooter;

    public function __construct($data)
    {
        $this->mergeFooter = [];
        $this->data = $data;
        $this->headings = [
            'No',
            'Kode',
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
    }
    public function collection()
    {
        //
        $fixData = [];
        $fixData[] = $this->headings;
        $fixData[] = ["", "", "", "", "qty", "rp/unit", "total", "qty", "rp/unit", "total", "qty", "rp/unit", "total", "qty", "rp/unit", "total"];
        $baris = 2;
        $this->kotakRange = 'A1:P' . (count($this->data['msg']) + $baris + 1);
        $totalSaldoAwal = 0;
        $totalMasuk = 0;
        $totalKeluar = 0;
        $totalSaldoAkhir = 0;
        foreach ($this->data['msg'] as $i => $item) {
            $baris++;
            $mutasiMasuk = data_get($this->data, 'mutasi_masuk.' . $item->id . '.qty', 0);
            $rupiahMasuk = data_get($this->data, 'mutasi_masuk.' . $item->id . '.total', 0);
            $hargaMasuk = $mutasiMasuk > 0 ? $rupiahMasuk / $mutasiMasuk : 0;
            $mutasiKeluar = data_get($this->data, 'mutasi_keluar.' . $item->id . '.qty', 0);
            $rupiahKeluar = data_get($this->data, 'mutasi_keluar.' . $item->id . '.total', 0);
            $hargaKeluar = $mutasiKeluar > 0 ? $rupiahKeluar / $mutasiKeluar : 0;
            $totalSaldoAwal += $item['awal_rupiah'];
            $totalMasuk += $rupiahMasuk;
            $totalKeluar += $rupiahKeluar;
            $totalSaldoAkhir += $item['akhir_rupiah'];
            $fixData[] = [
                $i + 1,
                $item->id,
                $item->name,
                $item->unit_default,
                ($item['awal_qty'] / $item->konversi),
                ($item['awal_qty'] > 0 ? ($item['awal_rupiah'] / $item['awal_qty']) : 0),
                ($item['awal_rupiah']),
                ($mutasiMasuk / $item->konversi),
                ($hargaMasuk),
                ($rupiahMasuk),
                ($mutasiKeluar / $item->konversi),
                ($hargaKeluar),
                ($rupiahKeluar),

                ($item['akhir_qty'] / $item->konversi),
                ($item['akhir_qty'] > 0 ? ($item['akhir_rupiah'] / $item['akhir_qty']) : 0),
                ($item['akhir_rupiah']),
            ];
        }

        $fixData[] = [
            "Total",
            "",
            "",
            "",
            
            ($totalSaldoAwal),
            "",
            "",

            ($totalMasuk),
            "",
            "",

            ($totalKeluar),
            "",
            "",

            ($totalSaldoAkhir),
            "",
            "",
        ];
        $baris++;
        $this->mergeFooter[] = ['start' => 'A' . $baris, 'end' => 'D' . $baris];
        $this->mergeFooter[] = ['start' => 'E' . $baris, 'end' => 'G' . $baris];
        $this->mergeFooter[] = ['start' => 'H' . $baris, 'end' => 'J' . $baris];
        $this->mergeFooter[] = ['start' => 'K' . $baris, 'end' => 'M' . $baris];
        $this->mergeFooter[] = ['start' => 'N' . $baris, 'end' => 'P' . $baris];

        return collect($fixData);
    }

      public function columnFormats(): array
    {
        return [
            'E' => '#,##0.00',
            'F' => '#,##0.00',
            'G' => '#,##0.00',
            'H' => '#,##0.00',
            'I' => '#,##0.00',
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
            'M' => '#,##0.00',
            'N' => '#,##0.00',
            'O' => '#,##0.00',
            'P' => '#,##0.00',
        ];
    }


    public function title(): string
    {
        return 'K.Stock ' . $this->data['year'] . '-' . $this->data['month'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $range = $this->kotakRange;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $akhirRow = (count($this->data['msg']) + 2);
                //menge Cell
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:G1');
                $sheet->mergeCells('H1:J1');
                $sheet->mergeCells('K1:M1');
                $sheet->mergeCells('N1:P1');
                $sheet->freezePane('E3');
                $sheet->getStyle('A1:P2')->getFont()->setBold(true);
                $sheet->getStyle('A1:P2')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1:P2')->getAlignment()->setVertical('center');
                $sheet->getStyle('E3:P' . $akhirRow)->getAlignment()->setHorizontal('right');

                foreach ($this->mergeFooter as  $row => $m) {
                    if ($row == 0) {
                        $sheet->getStyle($m['start'] . ':' . $m['end'])->getAlignment()->setHorizontal('center');
                    } else {
                        $sheet->getStyle($m['start'] . ':' . $m['end'])->getAlignment()->setHorizontal('right');
                    }
                    $sheet->mergeCells($m['start'] . ':' . $m['end']);
                    $sheet->getStyle($m['start'] . ':' . $m['end'])->getFont()->setBold(true);

                    $sheet->getStyle($m['start'] . ':' . $m['end'])->getAlignment()->setVertical('center');
                }
            },
        ];
    }
}
