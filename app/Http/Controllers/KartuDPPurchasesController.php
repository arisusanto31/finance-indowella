<?php

namespace App\Http\Controllers;

use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use App\Models\KartuDPPurchases;
use App\Models\KartuDPSales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuDPPurchasesController extends Controller
{
    public function index()
    {
        $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $year = getInput('year') ?? Date('Y');
        $view = view('kartu.kartu-dp-purchase');
        $view->month = $month;
        $view->year = $year;
        return $view;
    }

    public function createMutation(Request $request)
    {
        return KartuDPPurchases::createMutation($request);
    }

    public function createPelunasan(Request $request)
    {
        return KartuDPPurchases::createPelunasan($request);
    }

    public function getSummary()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        return KartuDPPurchases::getSummary($year, $month, 'invoice_pack_number');
    }

    public function refresh($id)
    {
        $kartu = KartuDPPurchases::find($id);
        $st = $kartu->createDetailKartuInvoice();
        if ($st['status'] == 0) {
            return $st;
        }

        return [
            'status' => 1,
            'msg' => $kartu
        ];
    }

    public function showDetail($nomer)
    {
        $view = view('kartu.modal._kartu-mutasi-dp-sales');
        $view->factur = $nomer;
        $kh = KartuDPPurchases::where('invoice_pack_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuDPPurchases::where('invoice_pack_number', $nomer)->get();
        $view->data = $data;

        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuDPPurchases::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name', 'codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }

    public function recalculateKartuDP($id)
    {
        $kartuDP = KartuDPPurchases::find($id);
        $kartuDP->recalculateSaldo();
        return [
            'status' => 1,
            'msg' => 'Recalculation successful'
        ];
    }
}
