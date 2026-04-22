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

class _NeracaLajurExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithEvents,
    WithColumnFormatting

{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $data, $baris, $barisParent;
    public function __construct($jsdata)
    {
        $this->data = $jsdata;
        $this->barisParent = [];
        $this->baris = count($this->data['msg']);
    }
    public function collection()
    {
        //
        $baris = 1;
        $fixData=[];
        foreach( $this->data['msg'] as $item){
            $baris++;
            if ($item['is_child'] == 1) {
                $fixData[] = [
                    $item['code_group'],
                    $item['name'],
                    $item['saldo_awal'],
                    $item['mutasi_debet'],
                    $item['mutasi_kredit'],
                    $item['saldo_akhir'],
                ];
            } else {
                if($item['level']==0){
                    //mbah e..
                    
                    $fixData[]=[""];
                    $baris++;
                }
                $this->barisParent[] = $baris;
                $fixData[] = [
                    $item['code_group'],
                    $item['name'],
                    '-',
                    '-',
                    '-',
                    '-',
                ];
            }
        }


        return collect($fixData);
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

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0.00',
            'D' => '#,##0.00',
            'E' => '#,##0.00',
            'F' => '#,##0.00',
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

                foreach($this->barisParent as $bp){
                    $sheet->getStyle('A'.$bp.':F'.$bp)->getFont()->setBold(true);
                }
            },
        ];
    }
}
