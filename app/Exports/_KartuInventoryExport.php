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

class _KartuInventoryExport implements FromCollection, WithTitle, WithEvents, ShouldAutoSize
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
            'Periode',
            'Nilai Perolehan',
            'Mutasi Pembelian'
        ];
        for ($i = 1; $i <= 12; $i++) {
            $this->headingsStart[] = 'Penyusutan ' . $data['year'] . '-' . toDigit($i, 2);
        }
        $this->headingsStart[] = 'Akumulasi Penyusutan';
        $this->headingsStart[] = 'Nilai Buku';
        $this->kotakKolom = [];
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
                    $item['periode'] . ' tahun',
                    format_price($item['nilai_perolehan']),
                    format_price($item['total_pembelian']),
                ];
                for ($j = 1; $j <= 12; $j++) {
                    $dataBaris[] = $item['penyusutan'] ?
                        format_price($item['penyusutan'][$this->data['year'] . '-' . toDigit($j, 2)] ?? 0) :
                        "-";
                }
                $dataBaris[] = format_price($item['total_penyusutan']);
                $dataBaris[] = format_price($this->data['saldo_buku_akhir'][$id]->nilai_buku ?? 0);
                $fixData[] = $dataBaris;
            }
            $end = $baris;
            $fixData[] = [];
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
}
