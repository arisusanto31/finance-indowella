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
        return KartuHutang::getSummary($year, $month, 'invoice_pack_number');
    }

    function getMutasiMasuk()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        return KartuHutang::getMutasi($year, $month, 'mutasi');
    }

    function getMutasiKeluar()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        return KartuHutang::getMutasi($year, $month, 'pelunasan');
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
