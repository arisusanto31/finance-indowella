<?php

namespace App\Http\Controllers;

use App\Models\KartuPiutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuPiutangController extends Controller
{


    public function index()
    {

        $view = view('kartu.kartu-piutang');
        return $view;
    }

    public function createMutation(Request $request)
    {
        return KartuPiutang::createMutation($request);
    }

    public function createPelunasan(Request $request)
    {
        return KartuPiutang::createPelunasan($request);
    }

    public function getSummary()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        if (!$year) $year = Date('Y');
        if (!$month) $month = Date('m');
        $date = $year . '-' . $month;
        $kartuPiutangAwal = KartuPiutang::whereIn('id', function ($q) use ($date) {
            $q->from('kartu_piutangs')->select(DB::raw('max(id)'))->where('created_at', '<', $date . '-01')->groupBy('package_number');
        })->where('amount_saldo_factur', '>', 0)->select('package_number', 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->get();

        $kartuPiutangBaru = KartuPiutang::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                'package_number',
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy('package_number', 'type')->get();

        $allFactur = collect($kartuPiutangAwal)->pluck('package_number')->merge(collect($kartuPiutangBaru)->pluck('package_number'))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuPiutangAwal->where('package_number', $factur)->first();
            $dataBaru = $kartuPiutangBaru->where('package_number', $factur)->first();
            $dataMutasi = optional($kartuPiutangBaru->where('package_number', $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuPiutangBaru->where('package_number', $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
            $saldoAwal = (optional($dataAktif)->amount_saldo_factur ?? 0);
            $dataFix = $dataAktif ? $dataAktif : $dataBaru;
            $data = [
                'person_name' => $dataFix->person->name,
                'person_type' => $dataFix->person_type,
                'invoice_date' => $dataFix->invoice_date,
                'factur_supplier_number' => $factur,
                'saldo_awal' => $saldoAwal,
                'mutasi' => $dataMutasi,
                'pelunasan' => abs($dataPelunasan),
                'saldo' => $saldoAwal + $dataMutasi + $dataPelunasan
            ];
            $customTable[] = $data;
        }

        return [
            'status' => 1,
            'msg' => $customTable,
            'month' => $month . '-' . $year
        ];
    }
}
