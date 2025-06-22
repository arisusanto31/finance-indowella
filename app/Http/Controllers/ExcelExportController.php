<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetReportExport;
use App\Models\ChartAccount;
use App\Models\InvoicePurchaseDetail;
use App\Models\InvoiceSaleDetail;
use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuHutang;
use App\Models\KartuPiutang;
use App\Services\ContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    //

    public static function getDataNeraca($month, $year)
    {
        $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:59');
        $query = ChartAccount::getRincianNeracaAt($date);
        if ($query['status'] == 0)
            return $query;
        $chartAccounts = $query['msg'];
        $laba = ChartAccount::getLabaBulanAt($date);
        $aset = collect($chartAccounts['Aset'])->sum('saldo');
        $kewajiban = collect($chartAccounts['Kewajiban'])->sum('saldo');
        $ekuitas = collect($chartAccounts['Ekuitas'])->sum('saldo');
        $jsdata = [
            'status' => 1,
            'date' => $date,
            'msg' => $chartAccounts,
            'Aset' => $aset,
            'Kewajiban' => $kewajiban,
            'Ekuitas' => $ekuitas,
            'laba_bulan' => $laba,
            'balance' => $aset - ($kewajiban + $ekuitas + $laba),
            'month' => $month,
            'year' => $year,
        ];
        return $jsdata;
    }

    public static function getDataNL($month, $year)
    {
        $data = ChartAccount::getRincianSaldoNeracaLajur($month, $year);

        $mutasi = ChartAccount::getRincianMutationNeracaLajur($month, $year);

        $data['msg'] = collect($data['msg'])->map(function ($item) use ($mutasi) {
            $item['mutasi_debet'] = 0;
            $item['mutasi_kredit'] = 0;
            if (isset($mutasi['msg'][$item['id']])) {
                $item['mutasi_debet'] = $mutasi['msg'][$item['id']]['total_debet'];
                $item['mutasi_kredit'] = $mutasi['msg'][$item['id']]['total_kredit'];
            }
            return $item;
        });
        $data['month'] = $month;
        $data['year'] = $year;
        return $data;
    }

    public static function getDataLR($month, $year)
    {
        $data = [];
        $totalPenjualan = 0;
        for ($i = $month; $i > 0; $i--) {
            $date = createCarbon($year . '-' . ($i + 1) . '-01');
            $lr = ChartAccount::getRincianLabaBulanAt($date)->keyBy('code_group');
            $penjualan = collect($lr)->where('code_group', '<', 500000)->sum('saldo_akhir');
            $totalPenjualan += $penjualan;
            $lr =  collect($lr)->map(function ($val) use ($penjualan) {
                $val['prosen'] = $penjualan > 0 ? getProsen($val['saldo_akhir'], $penjualan) : "--";
                return $val;
            });
            $lr['penjualan'] = $penjualan;


            $data[$year . '-' . toDigit($i, 2)] = $lr;
        }
        $charts = ChartAccount::aktif()->child()->withAlias()->where('chart_accounts.code_group', '>=', 400000)->select('chart_accounts.code_group', DB::raw('coalesce(ca.name,chart_accounts.name) as alias_name'))->get();
        return [
            'all_charts' => $charts,
            'msg' => $data,
            'total_penjualan' => $totalPenjualan,
            'year_month' => collect($data)->keys()->all(),
            'year' => $year
        ];
    }

    public static function getBukuKas($month, $year)
    {
        $coas = ChartAccount::aktif()->child()->where('code_group', '<', 120000)->pluck('code_group')->all();
        return self::_getMutationJournal($month, $year, $coas);
    }

    public static function getBukuMemo($month, $year)
    {
        $coas = ChartAccount::aktif()->child()->where('code_group', '>=', 120000)->pluck('code_group')->all();
        return self::_getMutationJournal($month, $year, $coas);
    }

    public static function _getMutationJournal($month, $year, $coas)
    {
        $indexDate = createCarbon($year . '-' . $month . '-01')->format('ymdHis00');
        $subData = Journal::select(DB::raw('max(index_date) as maxindex'), 'code_group')->where('index_date', '<', $indexDate)->whereIn('code_group', $coas)
            ->groupBy('code_group');
        $lastSaldoJournal = Journal::joinSub($subData, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.maxindex')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->pluck('journals.amount_saldo', 'journals.code_group')->all();

        $journals = Journal::whereIn('code_group', $coas)->whereMonth('created_at', $month)->whereYear('created_at', $year)->with(['lawanCode:name,code_group'])
            ->orderBy('index_date', 'asc')->get()->groupBy('code_group');
        $chartAccount = ChartAccount::whereIn('chart_accounts.code_group', $coas)->withAlias()->orderBy('chart_accounts.code_group')->pluck('alias_name', 'code_group');
        foreach ($coas as $coa) {
            if (!array_key_exists($coa, $journals->all())) {
                $journals[$coa] = [];
            }
        }
        $kotakBaris = [];
        $baris = 1;
        foreach ($chartAccount as $code => $name) {
            $baris++;
            $start = $baris;
            if (isset($journals[$code]))
                $end = count($journals[$code]) + 2 + $baris;
            else
                $end = $baris + 3;
            $kotakBaris[] = [
                'start' => $start,
                'end' => $end
            ];
            $baris = $end + 2;
        }
        return [
            'status' => 1,
            'msg' => $journals,
            'chart_accounts' => $chartAccount,
            'month' => $month,
            'year' => $year,
            'saldo_awal' => $lastSaldoJournal,
            'kotak_baris' => $kotakBaris,

        ];
    }

    public static function getPembelian($month, $year)
    {

        $inv = InvoicePurchaseDetail::from('invoice_purchase_details as d')
            ->join('invoice_packs as inv_pack', function ($join) {
                $join->on('inv_pack.invoice_number', '=', 'd.invoice_pack_number');
            })->whereMonth('d.created_at', $month)->whereYear('d.created_at', $year)->where('inv_pack.is_final', 1)->select('d.*')->get()->groupBy('invoice_pack_number');
        return [
            'month' => $month,
            'year' => $year,
            'msg' => $inv
        ];
    }
    public static function getPenjualan($month, $year)
    {

        $inv = InvoiceSaleDetail::from('invoice_sale_details as d')
            ->join('invoice_packs as inv_pack', function ($join) {
                $join->on('inv_pack.invoice_number', '=', 'd.invoice_pack_number');
            })->whereMonth('d.created_at', $month)->whereYear('d.created_at', $year)->where('inv_pack.is_final', 1)->select('d.*')->get()->groupBy('invoice_pack_number');
        return [
            'month' => $month,
            'year' => $year,
            'msg' => $inv
        ];
    }

    public static function getKartuPiutang($month, $year)
    {
        $summary = KartuPiutang::getSummary($year, $month);
        if ($summary['status'] == 0) {
            return $summary;
        }
        return [
            'msg' => $summary['msg'],
            'month' => $month,
            'year' => $year,
        ];
    }

    public static function getKartuHutang($month, $year)
    {
        $summary = KartuHutang::getSummary($year, $month);
        if ($summary['status'] == 0) {
            return $summary;
        }
        return [
            'msg' => $summary['msg'],
            'month' => $month,
            'year' => $year,
        ];
    }

    public static function getKartuInventory($year)
    {
        return InventoryController::getSummary($year);
    }

    public static function getKartuBDD($year)
    {
        return BDDController::getSummary($year);
    }

    public static function getKartuStock($month, $year)
    {
        return KartuStockController::getSummary($month, $year);
    }

    public static function getKartuBDP($month, $year)
    {
        return KartuBDP::getSummaryProduction($year, $month);
    }

    public static function getKartuBahanJadi($month, $year)
    {
        return KartuBahanJadi::getSummaryProduction($year, $month);
    }
    public function exportData()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');

        //    $view = view('exports.bukumemo');
        // $view->data = $data;
        // return $view;
        return Excel::download(new MultiSheetReportExport($month, $year), 'INDOKO PACKAGING ' . $year . '-' . $month . '.xlsx');
    }
}
