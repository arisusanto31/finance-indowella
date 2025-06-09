<?php

namespace App\Http\Controllers;

use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuKasController extends Controller
{

    public function index()
    {
        $view = view('kartu.kartu-kas');
        $kindKas = ChartAccount::where('code_group', '>', 110000)
            ->where('code_group', '<', 120000)
            ->whereRaw('code_group%1000=0')->pluck('name', 'code_group')->all();
        $view->kind_kas = $kindKas;
        $view->month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $view->year = getInput('year') ?? Date('Y');
        return $view;
    }

    public function getBukuKas()
    {
        $kindCodeGroup = getInput('kind');
        $month = getInput('month') ?? Date('m');
        $year = getInput('year') ?? Date('Y');

        $indexDate = createCarbon($year . '-' . $month . '-01')->format('ymdHis00');
        $coas = ChartAccount::aktif()->where('code_group', 'like', Journal::getPrimaryCode($kindCodeGroup) . '%')->pluck('code_group')->all();

        $subData = Journal::select(DB::raw('max(index_date) as maxindex'), 'code_group')->where('index_date', '<', $indexDate)->whereIn('code_group', $coas)
            ->groupBy('code_group');
        $lastSaldoJournal = Journal::joinSub($subData, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.maxindex')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->pluck('journals.amount_saldo', 'journals.code_group')->all();

        $journals = Journal::searchCOA($kindCodeGroup)->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->orderBy('index_date', 'asc')->get()->groupBy('code_group');

        $chartAccount = ChartAccount::aktif()->withAlias()->pluck('alias_name', 'code_group');
        foreach ($coas as $coa) {
            if (!array_key_exists($coa, $journals)) {
                $journals[$coa] = [];
            }
        }
        return [
            'status' => 1,
            'msg' => $journals,
            'chart_accounts' => $chartAccount,
            'month' => $month,
            'year' => $year,
            'code_group' => $kindCodeGroup,
            'saldo_awal' => $lastSaldoJournal,
        ];
    }

    public function addKas(Request $request)
    {

        $date = $request->input('date');
        $lawanCodeGroup = $request->input('lawan_code_group');
        $desc = $request->input('description');
        $amountDebet = $request->input('amount_debet');
        $amountKredit = $request->input('amount_kredit');
        $codeGroup = $request->input('code_group');
        $toko = Toko::first();
        if ($amountDebet && $amountKredit) {
            return ['status' => 0, 'msg' => 'tidak bisa memasukkan nilai debet dan kredit bersamaan'];
        }
        if ($amountDebet > 0) {
            //piutang bertambah
            $codeDebet = $codeGroup;
            $codeKredit = $lawanCodeGroup;
            $amount = $amountDebet;
        } else {
            $codeDebet = $lawanCodeGroup;
            $codeKredit = $codeGroup;
            $amount = $amountKredit;
        }

        $kredits = [
            [
                'code_group' => $codeKredit,
                'description' => $desc,
                'amount' => $amount,
                'reference_id' => null,
                'reference_type' => null,
                'toko_id' => $toko->id
            ],
        ];
        $debets = [
            [
                'code_group' => $codeDebet,
                'description' => $desc,
                'amount' => $amount,
                'reference_id' => null,
                'reference_type' => null,
                'toko_id' => $toko->id,

            ],
        ];
        $st = JournalController::createBaseJournal(new Request([
            'kredits' => $kredits,
            'debets' => $debets,
            'type' => 'keuangan',
            'date' => $date,
            'is_auto_generated' => 1,
            'title' => 'create mutation transaction',
            'url_try_again' => 'try_again',
            'is_backdate' => 1,


        ]));
        return $st;
    }
}
