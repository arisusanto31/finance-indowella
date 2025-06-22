<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class _PembelianExport implements FromCollection, WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */


    protected $data, $mergeKolom;
    public function __construct($data)
    {
        $this->data = $data;
        $this->mergeKolom = [];
    }

    public function collection()
    {
        //
        $baris = 2;
        $fixData = [];
        $row = 0;
        foreach ($this->data['msg'] as $invNumber => $detail) {
            $row++;
            $length = count($detail);
            if ($length > 1) {
                $this->mergeKolom[] = ['start' => $baris, 'end' => ($baris + $length - 1)];
            }
            foreach ($detail as $index => $d) {
                $baris++;
                if ($index == 0) {
                    $fixData[] = [
                        $row,
                        createCarbon($d->created_at)->format('Y-m-d'),
                        $d->supplier->name,
                        "",
                        "",
                        $d->invoice_pack_number,
                        "",
                        $d->stock_id,
                        $d->custom_stock_name,
                        $d->quantity,
                        $d->unit,
                        format_price($d->price),
                        format_price($d->total_price),
                        format_price(collect($detail)->sum('total_price'))
                    ];
                } else {
                    $fixData[] = [
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        $d->stock_id,
                        $d->custom_stock_name,
                        $d->quantity,
                        $d->unit,
                        format_price($d->price),
                        format_price($d->total_price),
                        ""
                    ];
                }
            }
        }

        return collect($fixData);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Supplier',
            'No. PO',
            'No. Surat Jalan',
            'No. Invoice',
            'No. Faktur Pajak',
            'Kode Barang',
            'Nama Barang',
            'jumlah',
            'satuan',
            'Harga',
            'DPP',
            'Total'
        ];
    }

    public function title(): string
    {
        return 'Pembelian ' . $this->data['year'] . '-' . $this->data['month'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = 'A1:' . $highestColumn . $highestRow;

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);
                $sheet->getStyle('A1:N1')->getFont()->setBold(true);
                $sheet->getStyle('A1:N1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1:N1')->getAlignment()->setVertical('center');
                $sheet->getStyle('J2:N' . $highestRow)->getAlignment()->setHorizontal('right');


                //menge Cell
                foreach ($this->mergeKolom as $m) {
                    $allKolomMerge = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'N'];
                    foreach ($allKolomMerge as $kolom) {
                        $rangeMerge = $kolom . $m['start'] . ':' . $kolom . $m['end'];
                        $sheet->mergeCells($rangeMerge);
                    }
                }
            },
        ];
    }
}
