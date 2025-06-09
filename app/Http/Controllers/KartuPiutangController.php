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
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $view->year = getInput('year') ? getInput('year') : Date('Y');


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
        return KartuPiutang::getSummary($year, $month, 'sales_order_number');
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
