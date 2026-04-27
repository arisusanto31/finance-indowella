<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetReportExport;
use App\Models\ChartAccount;
use App\Models\InvoicePurchaseDetail;
use App\Models\InvoiceSaleDetail;
use App\Models\Journal;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuDPSales;
use App\Models\KartuHutang;
use App\Models\KartuPiutang;
use App\Services\ContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Chart\Chart;

class ExcelExportController extends Controller
{
    //

    public static function getDataNeraca($month, $year)
    {
        $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:54');
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
            if (isset($mutasi['msg'][$item['code_group']])) {
                $item['mutasi_debet'] = $mutasi['msg'][$item['code_group']]['total_debet'];
                $item['mutasi_kredit'] = $mutasi['msg'][$item['code_group']]['total_kredit'];
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
            $date = createCarbon($year . '-' . ($i) . '-01')->endOfMonth()->subSeconds(5)->format('Y-m-d H:i:s');
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
        $coas = ChartAccount::aktif()->child()->where('code_group', '<', 120000)->orderBy('code_group')->pluck('code_group')->all();
        return self::_getMutationJournal($month, $year, $coas);
    }

    public static function getBukuMemo($month, $year)
    {
        $coas = ChartAccount::aktif()->child()->where('code_group', '>=', 120000)->orderBy('code_group')->pluck('code_group')->all();
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

        $journals = Journal::leftJoin('chart_accounts as lawan_code', 'lawan_code.code_group', '=', 'journals.lawan_code_group')->whereIn('journals.code_group', $coas)->whereMonth('journals.created_at', $month)->whereYear('journals.created_at', $year)
            ->orderBy('journals.index_date', 'asc')
            ->select('journals.*', 'lawan_code.name as lawan_code_name')
            ->get()->groupBy('code_group');
        $chartAccount = ChartAccount::withAlias()->orderBy('chart_accounts.code_group')->pluck('alias_name', 'code_group');
        foreach ($coas as $coa) {
            if (!array_key_exists($coa, $journals->all())) {
                $journals[$coa] = [];
            }
        }
        $kotakBaris = [];
        $baris = 1;
        foreach ($coas as $code) {
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
            $baris = $end + 3;
        }
        return [
            'status' => 1,
            'msg' => $journals,
            'coas' => $coas,
            'chart_accounts' => $chartAccount,
            'month' => $month,
            'year' => $year,
            'saldo_awal' => $lastSaldoJournal,
            'kotak_baris' => $kotakBaris,

        ];
    }

    public static function getPembelian($month, $year)
    {
        $date = createCarbon($year . '-' . $month . '-01');
        $indexStart = $date->copy()->startOfMonth()->format('ymdHis000');
        $indexEnd = $date->copy()->endOfMonth()->format('ymdHis999');

        $inv = InvoicePurchaseDetail::from('invoice_purchase_details as d')
            ->where('index_date', '>', $indexStart)
            ->where('index_date', '<', $indexEnd)
            ->select('d.*')->get()->groupBy('invoice_pack_number');
        return [
            'month' => $month,
            'year' => $year,
            'msg' => $inv
        ];
    }
    public static function getPenjualan($month, $year)
    {
        $date = createCarbon($year . '-' . $month . '-01');
        $indexStart = $date->copy()->startOfMonth()->format('ymdHis000');
        $indexEnd = $date->copy()->endOfMonth()->format('ymdHis999');

        $inv = InvoiceSaleDetail::from('invoice_sale_details as d')
            ->where('index_date', '>', $indexStart)
            ->where('index_date', '<', $indexEnd)
            ->select('d.*')->get()->groupBy('invoice_pack_number');
        return [
            'month' => $month,
            'year' => $year,
            'msg' => $inv
        ];
    }

    public static function getKartuPiutang($month, $year)
    {
        $summary = KartuPiutang::getSummary($year, $month, 'invoice_pack_number');
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
        $summary = KartuHutang::getSummary($year, $month, 'factur_supplier_number');
        if ($summary['status'] == 0) {
            return $summary;
        }
        return [
            'msg' => $summary['msg'],
            'month' => $month,
            'year' => $year,
        ];
    }

    public static function getKartuDPSales($month, $year)
    {
        $summary = KartuDPSales::getSummary($year, $month, 'sales_order_number');
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

    public static function analyze(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $neracaLajur = $request->input('neraca_lajur');
        $neraca = $request->input('neraca');
        $pembelian = $request->input('pembelian');
        $penjualan = $request->input('penjualan');
        $kartuPiutang = $request->input('kartu_piutang');
        $kartuHutang = $request->input('kartu_hutang');
        $kartuDPSales = $request->input('kartu_dpsales');
        $kartuInventory = $request->input('kartu_inventory');
        $kartuBDD = $request->input('kartu_bdd');
        $kartuStock = $request->input('kartu_stock');
        $kartuBDP = $request->input('kartu_bdp');
        $kartuBahanJadi = $request->input('kartu_bahan_jadi');
        $lr= $request->input('laba_rugi');
        $kas= $request->input('kas');

        //PROSES catatan kartu NL
        $totalPembelian = collect($pembelian['msg'])->sum(function ($item) {
            return collect($item)->sum('total_price');
        });
        $totalPenjualan = collect($penjualan['msg'])->sum(function ($item) {
            return collect($item)->sum('total_price');
        });
        $saldoAkhirPiutang = collect($kartuPiutang['msg'])->sum('saldo');
        $penambahanPiutang = collect($kartuPiutang['msg'])->sum('mutasi');
        $saldoAkhirUtang = collect($kartuHutang['msg'])->sum('saldo');
        $saldoDP = collect($kartuDPSales['msg'])->sum('saldo');
        $totalBebanInventory = collect($kartuInventory['msg'])->sum(function ($category) use ($month, $year) {
            return collect($category)->sum(function ($item) use ($month, $year) {
                return $item['penyusutan'][$year . '-' . $month] ?? 0;
            });
        });
        $totalBebanBDD = collect($kartuBDD['msg'])->sum(function ($category) use ($month, $year) {
            return collect($category)->sum(function ($item) use ($month, $year) {
                return $item['penyusutan'][$year . '-' . $month] ?? 0;
            });
        });
        $saldoAwalStock = collect($kartuStock['msg'])->sum('awal_rupiah');
        $saldoAkhirStock = collect($kartuStock['msg'])->sum('akhir_rupiah');
        $saldoAwalBDP = collect($kartuBDP['msg'])->sum(function ($category) {
            return collect($category)->sum(function ($item) {
                return $item['saldo_rupiah_awal'] ?? 0;
            });
        });
        $saldoAkhirBDP = collect($kartuBDP['msg'])->sum(function ($category) {
            return collect($category)->sum(function ($item) {
                return $item['saldo_rupiah_akhir'] ?? 0;
            });
        });
        $saldoAwalBahanJadi = collect($kartuBahanJadi['msg'])->sum(function ($category) {
            return collect($category)->sum(function ($item) {
                return $item['saldo_rupiah_awal'] ?? 0;
            });
        });
        $saldoAkhirBahanJadi = collect($kartuBahanJadi['msg'])->sum(function ($category) {
            return collect($category)->sum(function ($item) {
                return $item['saldo_rupiah_akhir'] ?? 0;
            });
        });

        $mutasiMasukStock = collect($kartuStock['mutasi_masuk'])->sum('total');
        $codePenjualan = ChartAccount::where('is_child', 1)->where('code_group', 'like', '4%')->pluck('code_group')->all();
        $sumNLPenjualan = collect($neracaLajur['msg'])->filter(function ($item) use ($codePenjualan) {
            return in_array($item['code_group'], $codePenjualan);
        })->sum('saldo_akhir');

        $codePersediaan = ChartAccount::where('is_child', 1)->where('code_group', 'like', '14%')->pluck('code_group')->all();
        $NLSumPersediaan = collect($neracaLajur['msg'])->filter(function ($item) use ($codePersediaan) {
            return in_array($item['code_group'], $codePersediaan);
        })->sum('saldo_akhir');
        $totalPersediaanAwal = $saldoAwalStock + $saldoAwalBDP + $saldoAwalBahanJadi;
        $totalPersediaan = $saldoAkhirStock + $saldoAkhirBDP + $saldoAkhirBahanJadi;
        $NLPersediaanBahanDagang = collect($neracaLajur['msg'])->where('code_group', 140001)->first()['saldo_akhir'] ?? 0;
        $NLPersediaanBahanBaku = collect($neracaLajur['msg'])->where('code_group', 140002)->first()['saldo_akhir'] ?? 0;
        $NLPersediaanBDP = collect($neracaLajur['msg'])->where('code_group', 140003)->first()['saldo_akhir'] ?? 0;
        $NLPersediaanBahanJadi = collect($neracaLajur['msg'])->where('code_group', 140004)->first()['saldo_akhir'] ?? 0;
        $codeKas= ChartAccount::where('is_child', 1)->where('code_group', 'like', '11%')->pluck('code_group')->all();
        $NLtotalKas= collect($neracaLajur['msg'])->filter(function($item) use ($codeKas){
            return in_array($item['code_group'], $codeKas);
        })->sum('saldo_akhir');
        $totalKas = collect($kas)->sum(function($code){
            return collect($code)->sum(function($item){
                return collect($item)->sortByDesc('index_date')->first()['amount_saldo'] ?? 0;
            });
        });
        $codePiutang= ChartAccount::where('is_child', 1)->where('code_group', 'like', '12%')->pluck('code_group')->all();
        $NLSumPiutang= collect($neracaLajur['msg'])->filter(function($item) use ($codePiutang){
            return in_array($item['code_group'], $codePiutang);
        })->sum('saldo_akhir');
        $codeUtang = ChartAccount::where('code_group','211000')->pluck('code_group')->all();
        $NLUtangUsaha = collect($neracaLajur['msg'])->filter(function ($item) use ($codeUtang) {
            return in_array($item['code_group'], $codeUtang);
        })->sum('saldo_akhir');
        $codeDP= ChartAccount::where('is_child', 1)->where('code_group', 'like', '214000')->pluck('code_group')->all();
        $NLSaldoDP= collect($neracaLajur['msg'])->filter(function($item) use ($codeDP){
            return in_array($item['code_group'], $codeDP);
        })->sum('saldo_akhir');

        $codeKewajiban= ChartAccount::where('is_child', 1)->where('code_group','like','2%')->pluck('code_group')->all();
        $NLSumKewajiban= collect($neracaLajur['msg'])->filter(function($item) use ($codeKewajiban){
            return in_array($item['code_group'], $codeKewajiban);
        })->sum('saldo_akhir');

        $neracaKewajiban = $neraca['Kewajiban']??0;
        $neracaLabaBulan= $neraca['laba_bulan']??0;
        $labaKartuLR = collect($lr['msg'][$year . '-' . toDigit($month, 2)])->sum('saldo_akhir');

        $codeHPP= ChartAccount::where('is_child', 1)->where('code_group', 'like', '6%')->pluck('code_group')->all();
        $NLSumHPP= collect($neracaLajur['msg'])->filter(function($item) use ($codeHPP){
            return in_array($item['code_group'], $codeHPP);
        })->sum('saldo_akhir');

        $saldoLaba= collect($neraca['msg']['Ekuitas'])->where('code_group',302000)->first()['saldo']??0; 
        $sumNeracaLaba= $neracaLabaBulan+ $saldoLaba;
        $sumKartuLR= collect($lr['msg'])->sum(function($tahun){
            return collect($tahun)->sum('saldo_akhir');
        });
        $data = [];
        $data[] = [
            'keterangan' => 'Total Pembelian vs Total Kartu Masuk',
            'data1' => $totalPembelian,
            'data2' => $mutasiMasukStock,
            'hasil' => abs($totalPembelian - $mutasiMasukStock) > 0.01 ? 'TIDAK SESUAI (' . ($totalPembelian - $mutasiMasukStock) . ')' : 'SESUAI'
        ];
        $data[] = [
            'keterangan' => 'Total Penjualan vs NL Sum penjualan',
            'data1' => $totalPenjualan,
            'data2' => $sumNLPenjualan,
            'hasil' => abs($totalPenjualan - $sumNLPenjualan) > 0.01 ? 'TIDAK SESUAI (' . ($totalPenjualan - $sumNLPenjualan) . ')' : 'SESUAI'
        ];
        $data[] = [
            'keterangan' => 'Total Persediaan vs NL Sum persediaan',
            'data1' => $totalPersediaan,
            'data2' => $NLSumPersediaan,
            'hasil' => abs($totalPersediaan - $NLSumPersediaan) > 0.01 ? 'TIDAK SESUAI (' . ($totalPersediaan - $NLSumPersediaan) . ')' : 'SESUAI'
        ];
        $data[] = [
            'keterangan' => 'Total K.Stock vs NL Persediaan Stock',
            'data1' => $saldoAkhirStock,
            'data2' => $NLPersediaanBahanDagang + $NLPersediaanBahanBaku,
            'hasil' => abs($saldoAkhirStock - ($NLPersediaanBahanDagang + $NLPersediaanBahanBaku)) > 0.01 ? 'TIDAK SESUAI (' . ($saldoAkhirStock - ($NLPersediaanBahanDagang + $NLPersediaanBahanBaku)) . ')' : 'SESUAI'
        ];
        $data[] = [
            'keterangan' => 'Total BDP vs NL Persediaan BDP',
            'data1' => $saldoAkhirBDP,
            'data2' => $NLPersediaanBDP,
            'hasil' => abs($saldoAkhirBDP - $NLPersediaanBDP) > 0.01 ? 'TIDAK SESUAI (' . ($saldoAkhirBDP - $NLPersediaanBDP) . ')' : 'SESUAI'
        ];
        $data[] = [
            'keterangan' => 'Total Bahan Jadi vs NL Persediaan Bahan Jadi',
            'data1' => $saldoAkhirBahanJadi,
            'data2' => $NLPersediaanBahanJadi,
            'hasil' => abs($saldoAkhirBahanJadi - $NLPersediaanBahanJadi) > 0.01 ? 'TIDAK SESUAI (' . ($saldoAkhirBahanJadi - $NLPersediaanBahanJadi) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=>'Total Kas vs NL Total Kas',
            'data1'=>$totalKas,
            'data2'=>$NLtotalKas,
            'hasil'=>abs($totalKas - $NLtotalKas) > 0.01 ? 'TIDAK SESUAI (' . ($totalKas - $NLtotalKas) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=> 'Saldo Akhir Piutang vs NL sum piutang',
            'data1'=> $saldoAkhirPiutang,
            'data2'=> $NLSumPiutang,
            'hasil'=>abs($saldoAkhirPiutang - $NLSumPiutang) > 0.01 ? 'TIDAK SESUAI (' . ($saldoAkhirPiutang - $NLSumPiutang) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=> 'Saldo Akhir Utang vs NL Utang Usaha',
            'data1'=> $saldoAkhirUtang,
            'data2'=> $NLUtangUsaha,
            'hasil'=>abs($saldoAkhirUtang - $NLUtangUsaha) > 0.01 ? 'TIDAK SESUAI (' . ($saldoAkhirUtang - $NLUtangUsaha) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=> 'Saldo DP vs NL Saldo DP',
            'data1'=> $saldoDP,
            'data2'=> $NLSaldoDP,
            'hasil'=>abs($saldoDP - $NLSaldoDP) > 0.01 ? 'TIDAK SESUAI (' . ($saldoDP - $NLSaldoDP) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=>'Kewajiban vs NL sum utang',
            'data1'=> $NLSumKewajiban,
            'data2'=> $neracaKewajiban,
            'hasil'=>abs($NLSumKewajiban - $neracaKewajiban) > 0.01 ? 'TIDAK SESUAI (' . ($NLSumKewajiban - $neracaKewajiban) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=>'Laba kartu LR vs Laba Neraca',
            'data1'=> $labaKartuLR,
            'data2'=> $neracaLabaBulan,
            'hasil'=>abs($neracaLabaBulan - ($labaKartuLR)) > 0.01 ? 'TIDAK SESUAI (' . ($neracaLabaBulan - ($labaKartuLR)) . ')' : 'SESUAI'
        ];
        $data[]=[
            'keterangan'=> 'penambahan piutang vs total penjualan',
            'data1'=> $penambahanPiutang,
            'data2'=> $totalPenjualan,
            'hasil'=>abs($penambahanPiutang - $totalPenjualan) > 0.01 ? 'TIDAK SESUAI (' . ($penambahanPiutang - $totalPenjualan) . ')' : 'SESUAI'  
        ];
        $data[]=[
            'keterangan'=> 'sum neraca laba vs sum kartu LR',
            'data1'=> $sumNeracaLaba,
            'data2'=> $sumKartuLR,
            'hasil'=>abs($sumNeracaLaba - $sumKartuLR) > 0.01 ? 'TIDAK SESUAI (' . ($sumNeracaLaba - $sumKartuLR) . ')' : 'SESUAI'  
        ];
        $data[]=[
            'keterangan'=>"AwalStock +pembelian- akhir stock vs HPP",
            'data1'=> $totalPersediaanAwal + $totalPembelian - $totalPersediaan,
            'data2'=> $NLSumHPP,
            'hasil'=>abs(($totalPersediaanAwal + $totalPembelian - $totalPersediaan) + $NLSumHPP) > 0.01 ? 'TIDAK SESUAI (' . (($totalPersediaanAwal + $totalPembelian - $totalPersediaan) + $NLSumHPP) . ')' : 'SESUAI'
        ];
        return [
            'status' => 1,
            'msg' => $data,
            'month' => $month,
            'year' => $year,
        ];
    }
    public function exportData()
    {
        $month = getInput('month') ?? date('m');
        $year = getInput('year') ?? date('Y');

        //    $view = view('exports.bukumemo');
        // $view->data = $data;
        // return $view;
        set_time_limit(0);
        return Excel::download(new MultiSheetReportExport($month, $year), 'INDOKO PACKAGING ' . $year . '-' . $month . '.xlsx');
    }
}
