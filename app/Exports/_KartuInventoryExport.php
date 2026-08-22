<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

use function Laravel\Prompts\form;

class _KartuInventoryExport implements FromCollection, WithTitle, WithEvents, WithColumnFormatting, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $kotakKolom, $headingsStart;

    public function __construct($data)
    {
        $this->data = $data;
        $this->headingsStart = [
            'No',
            'Nama Aset',
            'Qty',
            'Tanggal Perolehan',
            'Periode',
            'Nilai Perolehan',
            'Saldo Awal ' . $data['year'],
            'Mutasi Pembelian'

        ];
        for ($i = 1; $i <= $data['month']; $i++) {
            $this->headingsStart[] = 'Penyusutan ' . $data['year'] . '-' . toDigit($i, 2);
        }
        $this->headingsStart[] = 'Total Penyusutan';
        $this->headingsStart[] = 'Akumulasi akhir Penyusutan';
        $this->headingsStart[] = 'Nilai Buku';
        $this->kotakKolom = [];
    }

    public function columnFormats(): array
    {
        return [
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
            'Q' => '#,##0.00',
            'R' => '#,##0.00',
            'S' => '#,##0.00',
            'T' => '#,##0.00',
            'U' => '#,##0.00',
            'V' => '#,##0.00',
            'W' => '#,##0.00',

        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 40, //nama barang
            'C' => 10, // nama barang
            'D' => 15, // tanggal perolehan
            'E' => 15, // periode
            'F' => 15, // nilai perolehan
            'G' => 15, // saldo awal
            'H' => 15, //  mutasi pembelian
            'I' => 15, // penyusutan feb
            'J' => 15, // penyusutan mar
            'K' => 15, // penyusutan apr
            'L' => 15, // penyusutan mei
            'M' => 15, // penyusutan jun
            'N' => 15, // penyusutan jul
            'O' => 15, // penyusutan ags
            'P' => 15, // penyusutan sep
            'Q' => 15, // penyusutan okt
            'R' => 15, // penyusutan nov
            'S' => 15, // penyusutan des
            'T' => 15, // total penyusutan
            'U' => 20, // akumulasi akhir penyusutan
            'V' => 20, // nilai buku
            'W' => 20, // nilai buku

        ];
    }
    public function collection()
    {
        //
        $total = 0;
        $baris = 1;
        $fixData = [];
        $globalSusut = 0;
        $totalAset=0;
        $totalBuku=0;
        foreach ($this->data['msg'] as $jenis => $data) {
            $fixData[] = [$jenis];
            $baris++;
            $fixData[] = $this->headingsStart;
            $start = $baris;
            $i = 0;
            foreach ($data as $id => $item) {
                $baris++;
                $i++;
                $dataBaris = [
                    $i,
                    $item['name'],
                    $item['keterangan_qty_unit'],
                    $item['date'],
                    $item['periode'] . ' tahun',
                    ($item['nilai_perolehan']),
                    ($this->data['saldo_buku_awal'][$id]->nilai_buku ?? 0),
                    ($item['total_pembelian']),
                ];
                $totalSusut = 0;
                for ($j = 1; $j <= $this->data['month']; $j++) {
                    $nilai = $item['penyusutan'] ?
                        ($item['penyusutan'][$this->data['year'] . '-' . toDigit($j, 2)] ?? 0) :
                        0;
                    $dataBaris[] = $nilai == 0 ? '-' : ($nilai);
                    $totalSusut += $nilai;
                }
                $bukuAkhir = ($this->data['saldo_buku_akhir'][$id]->nilai_buku ?? 0);
                $dataBaris[] = ($item['total_penyusutan']);
                $dataBaris[] = $item['nilai_perolehan'] - $bukuAkhir;
                $dataBaris[] = $bukuAkhir;
                $fixData[] = $dataBaris;
                $globalSusut += $totalSusut;
                $totalAset += $item['nilai_perolehan'];
                $totalBuku += $bukuAkhir;
            }
            $end = $baris;
            $fixData[] = [""];
            $baris += 2;
            $this->kotakKolom[] = ['start' => $start, 'end' => $end];
        }
        $fixData[] = [
            'Resume ' . $this->data['year'].'-'.$this->data['month'],
        ];
        $baris++;
        $bukuAkhir = $totalBuku;
        $akumulasiAkhir = $totalAset - $bukuAkhir;
        $start = $baris;


        $fixData[] = [
            ' ',
            'Total Aset',
            format_price($totalAset)
        ];

        $fixData[] = [
            ' ',
            'Total Penyusutan',
            format_price($globalSusut)
        ];

        $fixData[] = [ 
            ' ',
            'Saldo Akhir Akumulasi Penyusutan',
            format_price($akumulasiAkhir)
        ];
        $fixData[] = [
            ' ',
            'Nilai Buku Akhir',
            format_price($bukuAkhir)
        ];
        $end = $baris + 4;


        return collect($fixData);
    }


    public function title(): string
    {
        return 'AT ' . $this->data['year'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('H1');

                //menge Cell
                foreach ($this->kotakKolom as $m) {
                    $range = 'A' . ($m['start']) . ':V' . ($m['end']);
                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
                    $colHeader = 'A' . ($m['start'] - 1) . ':V' . ($m['start']);
                    $sheet->getStyle($colHeader)->getFont()->setBold(true);
                    $sheet->getStyle($colHeader)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($colHeader)->getAlignment()->setVertical('center');
                    $sheet->getStyle('E' . ($m['start'] + 1) . ':V' . $m['end'])->getAlignment()->setHorizontal('right');
                }
            },


        ];
    }
}
