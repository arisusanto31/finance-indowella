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

class _AnalyzeExport implements FromCollection, WithHeadings, WithTitle, WithEvents, ShouldAutoSize, WithColumnFormatting
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
        $fixData = [];
        foreach ($this->data['msg'] as $d) {
            $fixData[] = [
                $d['keterangan'],
                $d['data1'],
                $d['data2'],
                $d['hasil']
            ];
        }
        return collect($fixData);
    }
    public function columnFormats(): array
    {
        return [
            'B' => '#,##0.00',
            'C' => '#,##0.00',
        ];
    }
    public function headings(): array
    {
        return [
            'Keterangan',
            'Data 1',
            'Data 2',
            'Hasil',
        ];
    }

    public function title(): string
    {
        return 'catatan';
    }

    public function registerEvents(): array
    {
        return [
            // AfterSheet::class => function (AfterSheet $event) {
            //     $sheet = $event->sheet->getDelegate();
            //     $highestRow = $sheet->getHighestRow();
            //     $highestColumn = $sheet->getHighestColumn();
            //     $range = 'A1:' . $highestColumn . $highestRow;

            //     $sheet->getStyle($range)->applyFromArray([
            //         'borders' => [
            //             'allBorders' => [
            //                 'borderStyle' => Border::BORDER_THIN,
            //                     'color' => ['argb' => '000000'],
            //                 ],
            //             ],
            //         ]);

            //         $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            //         $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal('center');
            //         $sheet->getStyle('A1:I1')->getAlignment()->setVertical('center');
            //         $sheet->getStyle('E2:I' . $highestRow)->getAlignment()->setHorizontal('right');
            //         $sheet->getStyle('A' . $highestRow . ':I' . $highestRow)->getFont()->setBold(true);


            //         //menge Cell
            //         $sheet->mergeCells($this->rangeMerge);
            // },
        ];
    }
}
