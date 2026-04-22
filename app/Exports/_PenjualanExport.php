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

class _PenjualanExport implements FromCollection, WithHeadings, WithTitle, WithEvents, ShouldAutoSize,WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */


    protected $data, $mergeKolom, $mergeFooter;
    public function __construct($data)
    {
        $this->data = $data;
        $this->mergeKolom = [];
        $this->mergeFooter = [];
    }

      public function columnFormats(): array
    {
        return [
            'J' => '#,##0.00',
            'K' => '#,##0.00',
            'L' => '#,##0.00',
        ];
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
                        optional($d->customer)->name ?? "??",
                        $d->invoice_pack_number,
                        "",
                        $d->stock_id,
                        $d->custom_stock_name,
                        $d->quantity,
                        $d->unit,
                        ($d->price),
                        ($d->total_price),
                        (collect($detail)->sum('total_price'))
                    ];
                } else {
                    $fixData[] = [
                        "",
                        "",
                        "",
                        "",
                        "",
                        $d->stock_id,
                        $d->custom_stock_name,
                        $d->quantity,
                        $d->unit,
                        ($d->price),
                        ($d->total_price),
                        ""
                    ];
                }
            }
        }
        $totalPenjualan = collect($this->data['msg'])->map(function ($detail) {
            return collect($detail)->sum('total_price');
        })->sum();
        $fixData[] = [
            "Total Penjualan",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            $totalPenjualan
        ];
        $this->mergeFooter[] = ['start' => 'A' . $baris, 'end' => 'G' . $baris];
        $baris++;

        return collect($fixData);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Customer',
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
        return 'Penjualan ' . $this->data['year'] . '-' . $this->data['month'];
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

                $sheet->getStyle('A1:L1')->getFont()->setBold(true);
                $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1:L1')->getAlignment()->setVertical('center');
                $sheet->getStyle('J2:L' . $highestRow)->getAlignment()->setHorizontal('right');


                //menge Cell
                foreach ($this->mergeKolom as $m) {
                    $allKolomMerge = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'N', 'L'];
                    foreach ($allKolomMerge as $kolom) {
                        $rangeMerge = $kolom . $m['start'] . ':' . $kolom . $m['end'];
                        $sheet->mergeCells($rangeMerge);
                    }
                }

                foreach ($this->mergeFooter as $m) {
                    $sheet->mergeCells($m['start'] . ':' . $m['end']);
                    $sheet->getStyle($m['start'] . ':' . $m['end'])->getFont()->setBold(true);
                    $sheet->getStyle($m['start'] . ':' . $m['end'])->getAlignment()->setHorizontal('center');
                    $sheet->getStyle($m['start'] . ':' . $m['end'])->getAlignment()->setVertical('center');
                }
            },
        ];
    }
}
