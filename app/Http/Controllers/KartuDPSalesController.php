<?php

namespace App\Http\Controllers;

use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use App\Models\KartuDPSales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuDPSalesController extends Controller
{
    public function index()
    {
        $view = view('kartu.kartu-dp-sale');
        return $view;
    }

    public function createMutation(Request $request)
    {
        return KartuDPSales::createMutation($request);
    }

    public function createPelunasan(Request $request)
    {
        return KartuDPSales::createPelunasan($request);
    }

    public function getSummary()
    {
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');
        if (!$year) $year = Date('Y');
        if (!$month) $month = Date('m');
        $date = $year . '-' . $month;
        $kartuPiutangAwal = KartuDPSales::whereIn('id', function ($q) use ($date) {
            $q->from('kartu_dp_sales')->select(DB::raw('max(id)'))->where('created_at', '<', $date . '-01')->groupBy('sales_order_number');
        })->where('amount_saldo_factur', '>', 0)->select('sales_order_number', 'invoice_date', 'type', 'amount_saldo_factur', 'person_id', 'person_type')->get();

        $kartuPiutangBaru = KartuDPSales::whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->select(
                DB::raw('sum(amount_debet-amount_kredit) as total_amount '),
                'sales_order_number',
                'type',
                'person_id',
                'invoice_date',
                'person_type',
            )->groupBy('sales_order_number', 'type')->get();

        $allFactur = collect($kartuPiutangAwal)->pluck('sales_order_number')->merge(collect($kartuPiutangBaru)->pluck('sales_order_number'))->unique()->all();
        $customTable = [];
        foreach ($allFactur as $factur) {
            $dataAktif = $kartuPiutangAwal->where('sales_order_number', $factur)->first();
            $dataBaru = $kartuPiutangBaru->where('sales_order_number', $factur)->first();
            $dataMutasi = optional($kartuPiutangBaru->where('sales_order_number', $factur)->where('type', 'mutasi')->first())->total_amount ?? 0;
            $dataPelunasan = optional($kartuPiutangBaru->where('sales_order_number', $factur)->where('type', 'pelunasan')->first())->total_amount ?? 0;
            $saldoAwal = (optional($dataAktif)->amount_saldo_factur ?? 0);
            $dataFix = $dataAktif ? $dataAktif : $dataBaru;
            $data = [
                'person_name' => $dataFix->person->name,
                'person_type' => $dataFix->person_type,
                'invoice_date' => $dataFix->invoice_date,
                'sales_order_number' => $factur,
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

    public function refresh($id)
    {
        $kartu = KartuDPSales::find($id);
        $detail = DetailKartuInvoice::where('kartu_id', $kartu->id)->where('kartu_type', get_class($kartu))->first();;
        if (!$detail) {
            $st = $kartu->createDetailKartuInvoice();
            if ($st['status'] == 0) {
                return $st;
            }
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
        $kh = KartuDPSales::where('sales_order_number', $nomer)->orderBy('created_at', 'desc')->first();
        $view->person = $kh->person;
        $data = KartuDPSales::where('sales_order_number', $nomer)->get();
        $view->data = $data;
        
        return $view;
    }

    public function searchLinkJournal()
    {
        $journals = Journal::where('reference_model', KartuDPSales::class)
            ->whereNull('verified_by')->with(['codeGroupData:code_group,name', 'codeGroupLawanData:code_group,name'])->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }
}
