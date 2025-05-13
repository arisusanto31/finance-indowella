<?php

namespace App\Http\Controllers;

use App\Models\Journal;
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
            $q->from('kartu_piutangs')->select(DB::raw('max(id)'))->where('created_at', '<', $date . '-01')->groupBy('invoice_pack_number');
        })->where('amount_saldo_factur', '>', 0)->select('invoice_pack_number', 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->get();

        $kartuPiutangBaru = KartuPiutang::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                'invoice_pack_number',
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy('invoice_pack_number', 'type')->get();

        $allFactur = collect($kartuPiutangAwal)->pluck('invoice_pack_number')->merge(collect($kartuPiutangBaru)->pluck('invoice_pack_number'))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuPiutangAwal->where('invoice_pack_number', $factur)->first();
            $dataBaru = $kartuPiutangBaru->where('invoice_pack_number', $factur)->first();
            $dataMutasi = optional($kartuPiutangBaru->where('invoice_pack_number', $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuPiutangBaru->where('invoice_pack_number', $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
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
        $view = view('kartu.modal._kartu-mutasi-piutang');
        $view->factur = $nomer;
        $kh = KartuPiutang::where('invoice_pack_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuPiutang::where('invoice_pack_number', $nomer)->get();
        $view->data = $data;
        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuPiutang::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name', 'codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }

    public function refresh($id)
    {
        $kartu = KartuPiutang::find($id);
        $detail = $kartu->createDetailKartuInvoice();
        if ($detail['status'] == 0) {
            return $detail;
        }
        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }
}
