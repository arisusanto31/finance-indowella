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

    protected $data, $akhirBaris, $barisHeadings, $headings1, $headings2;

    public function __construct($data)
    {
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
        foreach ($this->data['msg'] as $numProd => $items) {
            $fixData[] = [$numProd];
            $baris++;
            $fixData[] = $this->headings1;
            $baris++;
            $this->barisHeadings[] = $baris;
            $fixData[] = $this->headings2;
            $baris++;
            $this->akhirBaris[] =   (count($items) + $baris);

            foreach ($items as $i => $item) {

                $mutasiMasuk = data_get($this->data, 'mutasi_masuk.' . $numProd . '.' . $item['id'] . '.qty', 0);
                $rupiahMasuk = data_get($this->data, 'mutasi_masuk.' . $numProd . '.' . $item['id'] . '.total', 0);
                $hargaMasuk = $mutasiMasuk > 0 ? $rupiahMasuk / $mutasiMasuk : 0;
                $mutasiKeluar = data_get($this->data, 'mutasi_keluar.' . $numProd . '.' . $item['id'] . '.qty', 0);
                $rupiahKeluar = data_get($this->data, 'mutasi_keluar.' . $numProd . '.' . $item['id'] . '.total', 0);
                $hargaKeluar = $mutasiKeluar > 0 ? $rupiahKeluar / $mutasiKeluar : 0;
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
            $fixData[] = [""];
            $baris++;
        }

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
            },
        ];
    }

    public function title(): string
    {
        return 'K.BDP ' . $this->data['year'] . '-' . $this->data['month'];
    }
}
