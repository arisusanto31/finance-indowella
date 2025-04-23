<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\KartuHutang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuHutangController extends Controller
{

    public function index()
    {

        $view = view('kartu.kartu-hutang');
        return $view;
    }

    public function createMutation(Request $request)
    {
        return KartuHutang::createMutation($request);
    }

    public function createPelunasan(Request $request)
    {
        return KartuHutang::createPelunasan($request);
    }

    public function getSummary()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        if (!$year) $year = Date('Y');
        if (!$month) $month = Date('m');
        $date = $year . '-' . $month;
        $kartuHutangAwal = KartuHutang::whereIn('id', function ($q) use ($date) {
            $q->from('kartu_hutangs')->select(DB::raw('max(id)'))->where('created_at', '<', $date . '-01')->groupBy('factur_supplier_number');
        })->where('amount_saldo_factur', '>', 0)->select('factur_supplier_number', 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->with('person:id,name')->get();

        $kartuHutangBaru = KartuHutang::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                'factur_supplier_number',
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy('factur_supplier_number', 'type')->get();

        $allFactur = collect($kartuHutangAwal)->pluck('factur_supplier_number')->merge(collect($kartuHutangBaru)->pluck('factur_supplier_number'))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuHutangAwal->where('factur_supplier_number', $factur)->first();
            $dataBaru = $kartuHutangBaru->where('factur_supplier_number', $factur)->first();
            $dataMutasi = optional($kartuHutangBaru->where('factur_supplier_number', $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuHutangBaru->where('factur_supplier_number', $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
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

    public function showDetail($nomer)
    {
        $view = view('kartu.modal._kartu-mutasi-hutang');
        $view->factur = $nomer;
        $kh = KartuHutang::where('factur_supplier_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuHutang::where('factur_supplier_number', $nomer)->get();
        $view->data = $data;
        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuHutang::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name','codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }
}
