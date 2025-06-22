<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class _NeracaLajurExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $baris;
    public function __construct($jsdata)
    {
        $this->data = $jsdata;
        $this->baris = count($this->data['msg']);
    }
    public function collection()
    {
        //
        return collect($this->data['msg'])->map(function ($item) {
            return [
                $item['code_group'],
                $item['name'],
                format_price($item['saldo_awal']),
                format_price($item['mutasi_debet']),
                format_price($item['mutasi_kredit']),
                format_price($item['saldo_akhir']),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Nama COA',
            'Saldo Awal',
            'Mutasi Debet',
            'Mutasi Kredit',
            'Saldo Akhir',
        ];
    }

    public function title(): string
    {
        return 'NL ' . $this->data['year'] . '-' . $this->data['month'];
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
                $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A1:F1')->getAlignment()->setVertical('center');
                $sheet->getStyle('C2:F' . $this->baris)->getAlignment()->setHorizontal('right');
            },
        ];
    }
}
