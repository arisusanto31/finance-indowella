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

class _KartuHutangExport implements FromCollection, WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $rangeMerge;

    public function __construct($data)
    {
        $this->data = $data;
        $this->rangeMerge = '';
    }
    public function collection()
    {
        //
        $total = 0;
        $baris = 1;
        $data = collect($this->data['msg'])->map(function ($item, $i) use (&$total, &$baris) {
            $total += $item['saldo'];
            $baris++;
            return [
                $i + 1,
                $item['person_name'],
                createCarbon($item['invoice_date'])->format('Y-m-d'),
                $item['invoice_pack_number'],
                format_price($item['saldo_awal']),
                format_price($item['mutasi']),
                format_price($item['pelunasan']),
                format_price($item['saldo']),
                format_price($total)
            ];
        });
        $data[] = [
            'Total',
            '',
            '',
            '',
            format_price(collect($this->data['msg'])->sum('saldo_awal')),
            format_price(collect($this->data['msg'])->sum('mutasi')),
            format_price(collect($this->data['msg'])->sum('pelunasan')),
            format_price(collect($this->data['msg'])->sum('saldo')),
            format_price($total)
        ];
        $baris++;
        $this->rangeMerge = 'A' . ($baris) . ':D' . ($baris);
        info($this->rangeMerge);
        return $data;
    }
    public function headings(): array
    {
        return [
            'No',
            'Person',
            'Tanggal',
            'No Invoice',
            'Saldo Awal',
            'Mutasi',
            'Pelunasan',
            'Saldo',
            'Akumulasi Saldo',
        ];
    }

    public function title(): string
    {
        return 'Utang ' . $this->data['year'] . '-' . $this->data['month'];
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
                $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1:I1')->getAlignment()->setVertical('center');
                $sheet->getStyle('E2:I' . $highestRow)->getAlignment()->setHorizontal('right');
                $sheet->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);
                

                //menge Cell
                $sheet->mergeCells($this->rangeMerge);
            },
        ];
    }
}
