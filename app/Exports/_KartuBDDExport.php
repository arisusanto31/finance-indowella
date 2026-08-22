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

class _KartuBDDExport implements FromCollection, WithTitle, WithEvents, WithColumnWidths, WithColumnFormatting
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
            'Nama ',
            'tanggal perolehan',
            'Periode',
            'Nilai Perolehan',
            'Saldo Awal ' . $data['year'],
            'Mutasi Pembayaran'
        ];
        for ($i = 1; $i <= $data['month']; $i++) {
            $this->headingsStart[] = 'Amortisasi ' . $data['year'] . '-' . toDigit($i, 2);
        }
        $this->headingsStart[] = 'Total  Amortisasi';
        $this->headingsStart[] = 'Nilai Akhir';
        $this->kotakKolom = [];
    }
      public function columnFormats(): array
    {
        return [
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
            'Q' => '#,##0.00',
            'R' => '#,##0.00',
            'S' => '#,##0.00',
            'T' => '#,##0.00',
            'U' => '#,##0.00',
            'V' => '#,##0.00',
            'W' => '#,##0.00',

        ];
    }
    public function collection()
    {
        //
        $total = 0;
        $baris = 1;
        $fixData = [];
        $totalBuku=0;
        $totalSusut=0;
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
                    $item['date'],
                    $item['periode'] . ' tahun',
                    format_price($item['nilai_perolehan']),
                    format_price($this->data['saldo_buku_awal'][$id]->nilai_buku ?? 0),
                    format_price($item['total_pembelian']),
                ];
                for ($j = 1; $j <= $this->data['month']; $j++) {
                    $dataBaris[] = $item['penyusutan'] ?
                        format_price($item['penyusutan'][$this->data['year'] . '-' . toDigit($j, 2)] ?? 0) :
                        "-";
                }
                $bukuAkhir = ($this->data['saldo_buku_akhir'][$id]->nilai_buku ?? 0);
                $dataBaris[] = format_price($item['total_penyusutan']);
                $dataBaris[] = format_price($bukuAkhir);
                $fixData[] = $dataBaris;
                $totalSusut += $item['total_penyusutan'];
                $totalBuku += $bukuAkhir;
            }
            $end = $baris;
            $fixData[] = [];
            $baris += 2;
            $this->kotakKolom[] = ['start' => $start, 'end' => $end];
        }

        $fixData[] = [
            'Resume ' . $this->data['year'].'-'.$this->data['month'],
        ];
        $baris++;
        $bukuAkhir = $totalBuku;
        $start = $baris;


        $fixData[] = [
            ' ',
            'Total Penyusutan',
            format_price($totalSusut)
        ];

        $fixData[] = [
            ' ',
            'Nilai Buku Akhir',
            format_price($bukuAkhir)
        ];
        $end = $baris + 2;

        return collect($fixData);
    }


    public function title(): string
    {
        return 'BDD ' . $this->data['year'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('G1');

                //menge Cell
                foreach ($this->kotakKolom as $m) {
                    $range = 'A' . ($m['start']) . ':T' . ($m['end']);
                    $sheet->getStyle($range)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);

                    $colHeader = 'A' . ($m['start'] - 1) . ':T' . ($m['start']);
                    $sheet->getStyle($colHeader)->getFont()->setBold(true);
                    $sheet->getStyle($colHeader)->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($colHeader)->getAlignment()->setVertical('center');
                    $sheet->getStyle('E' . ($m['start'] + 1) . ':T' . $m['end'])->getAlignment()->setHorizontal('right');
                }
            },
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,
            'B' => 40,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 20,
            'R' => 20,
            'S' => 20,
            'T' => 20
        ];
    }
}
