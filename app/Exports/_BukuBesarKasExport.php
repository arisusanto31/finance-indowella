<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class _BukuBesarKasExport implements FromView, WithTitle, WithEvents, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($jsdata)
    {
        $this->data = $jsdata;
    }

    public function view(): View
    {
        return view('exports.bukubesar', [
            'data' => $this->data,
        ]);
    }

    public function title(): string
    {
        return 'Kas ' . $this->data['year'] . '-' . $this->data['month'];
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($this->data['kotak_baris'] as $kotak) {
                    $range = 'A' . $kotak['start'] . ':H' . $kotak['end']; // misal kolom sampai P
                    $event->sheet->getDelegate()->getStyle($range)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
