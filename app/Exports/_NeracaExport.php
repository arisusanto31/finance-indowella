<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class _NeracaExport implements FromView,WithTitle,ShouldAutoSize
{

    protected $data;

    public function __construct($jsdata)
    {
        $this->data = $jsdata;
    }
    public function view(): View
    {
        $aset = $this->data['msg']['Aset'] ?? [];
        $kewajiban = $this->data['msg']['Kewajiban'] ?? [];
        $ekuitas = $this->data['msg']['Ekuitas'] ?? [];
        $laba_bulan = $this->data['laba_bulan'] ?? 0;
        $totalAset = collect($aset)->sum('saldo');
        $totalPassiva = collect($kewajiban)->sum('saldo') + collect($ekuitas)->sum('saldo') + $laba_bulan;
        return view('exports.neraca', [
            'aset' => $aset,
            'kewajiban' => $kewajiban,
            'ekuitas' => $ekuitas,
            'laba_bulan' => $laba_bulan,
            'totalAset' => $totalAset,
            'totalPassiva' => $totalPassiva,
        ]);
    }

    public function title(): string
    {
        return 'Neraca '.$this->data['year'].'-'.$this->data['month'];
    }
}
