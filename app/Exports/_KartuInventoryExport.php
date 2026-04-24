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

class _KartuInventoryExport implements FromCollection, WithTitle, WithEvents, ShouldAutoSize, WithColumnFormatting
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
            'Mutasi Pembelian'
        ];
        for ($i = 1; $i <= 12; $i++) {
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

        ];
    }
    public function collection()
    {
        //
        $total = 0;
        $baris = 1;
        $fixData = [];
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
                    ($item['total_pembelian']),
                ];
                $totalSusut = 0;
                for ($j = 1; $j <= 12; $j++) {
                    $nilai = $item['penyusutan'] ?
                        ($item['penyusutan'][$this->data['year'] . '-' . toDigit($j, 2)] ?? 0) :
                        0;
                    $dataBaris[] = $nilai == 0 ? '-' : ($nilai);
                    $totalSusut += $nilai;
                }
                $dataBaris[] = ($totalSusut);
                $dataBaris[] = ($item['total_penyusutan']);
                $dataBaris[] = ($this->data['saldo_buku_akhir'][$id]->nilai_buku ?? 0);
                $fixData[] = $dataBaris;
            }
            $end = $baris;
            $fixData[] = [""];
            $baris += 2;
            $this->kotakKolom[] = ['start' => $start, 'end' => $end];
        }
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
