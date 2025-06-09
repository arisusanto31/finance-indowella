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
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $view->year = getInput('year') ? getInput('year') : Date('Y');
        
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
        $kartuHutangAwal = KartuHutang::whereIn('index_date', function ($q) use ($date) {
            $q->from('kartu_hutangs')->select(DB::raw('max(id)'))->where('created_at', '<', $date . '-01')->groupBy('invoice_pack_number');
        })->where('amount_saldo_factur', '<>', 0)->select('invoice_pack_number', 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->with('person:id,name')->get();

        $kartuHutangBaru = KartuHutang::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                'invoice_pack_number',
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy('invoice_pack_number', 'type')->get();

        $allFactur = collect($kartuHutangAwal)->pluck('invoice_pack_number')->merge(collect($kartuHutangBaru)->pluck('invoice_pack_number'))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuHutangAwal->where('invoice_pack_number', $factur)->first();
            $dataBaru = $kartuHutangBaru->where('invoice_pack_number', $factur)->first();
            $dataMutasi = optional($kartuHutangBaru->where('invoice_pack_number', $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuHutangBaru->where('invoice_pack_number', $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
            $saldoAwal = (optional($dataAktif)->amount_saldo_factur ?? 0);
            $dataFix = $dataAktif ? $dataAktif : $dataBaru;

            $data = [
                'person_name' => $dataFix->person->name,
                'person_type' => $dataFix->person_type,
                'invoice_date' => $dataFix->invoice_date,
                'invoice_pack_number' => $factur,
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
        $kh = KartuHutang::where('invoice_pack_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuHutang::where('invoice_pack_number', $nomer)->get();
        $view->data = $data;
        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuHutang::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name', 'codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }
}
