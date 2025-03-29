<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateJournalJob;
use App\Jobs\UpdateLawanCodeJournalJob;
use App\Models\BookJournal;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\JournalJobFailed;
use App\Models\JournalKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class JournalController extends Controller
{
    //




    public function neraca()
    {
        $view = view('main.neraca');

        $starttime = microtime(true);
        $date = getInput('date') ? getInput('date') : carbonDate();
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
            'time' => microtime(true) - $starttime,
            'date' => $date,
            'msg' => $chartAccounts,
            'Aset' => $aset,
            'Kewajiban' => $kewajiban,
            'Ekuitas' => $ekuitas,
            'laba_bulan' => $laba,
            'balance' => $aset - ($kewajiban + $ekuitas + $laba)
        ];
        $view->jsdata = $jsdata;

        return $view;
    }

    public function neracalajur()
    {
        $view = view('main.neraca-lajur');
        $month = getInput('month') ? getInput('month') : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('y') : Date('y');
        $view->data =  ChartAccount::getRincianSaldoNeracaLajur($month, $year);
        return $view;
    }
    public function getMutasiNeracaLajur()
    {
        $month = getInput('month') ? getInput('month') : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('y') : Date('y');
        return ChartAccount::getRincianMutationNeracaLajur($month, $year);
    }


    public function getListMutasiJurnal()
    {

        $year = getInput('year') ?: Date('y');
        $month = getInput('month') ?: Date('m');
        $thedate = createCarbon($year . '-' . $month . '-01');
        $startIndex = intval($thedate->format('ym') . '0000000000');
        $finishIndex = intval($thedate->addMonth()->format('ym') . '0000000000');
        $perPage = 30;
        $search = getInput('search');
        $coa = getInput('coa');
        $nameCOA = getInput('namaCOA');
        $number = getInput('journal_number');
        $paginateJournalNumber = Journal::searchNote($search)->searchCOA($coa)->searchNumber($number)->searchNameCOA($nameCOA)->where('index_date', '>', $startIndex)->sortindex()->where('index_date', '<', $finishIndex)->select('journal_number')->distinct()->paginate($perPage);
        $countJournal = Journal::searchNote($search)->searchCOA($coa)->searchNumber($number)->searchNameCOA($nameCOA)->where('index_date', '>', $startIndex)->sortindex()->where('index_date', '<', $finishIndex)->select('journal_number')->distinct()->get()->count();
        $maxPage = ceil($countJournal / $perPage);
        $journalNumbers = collect($paginateJournalNumber->items())->pluck('journal_number')->all();
        $journal = Journal::whereIn('journal_number', $journalNumbers)->sortIndex()->get()->groupBy('journal_number');
        $chartAccount = ChartAccount::aktif()->pluck('name', 'code_group');
        return [
            'status' => 1,
            'msg' => $journal,
            'max_page' => $maxPage,
            'chart_accounts' => $chartAccount,
            'start_index' => $startIndex,
            'finish_index' => $finishIndex
        ];
    }

    public function labarugi()
    {
        $view = view('main.laba-rugi');
        $date =  getInput('date') ? getInput('date') : carbonDate();
        $labarugi = ChartAccount::getRincianLabaBulanAt($date);
        $data = [
            'status' => 1,
            'msg' => $labarugi,
            'laba_bulan' => round(collect($labarugi)->where('is_child', 1)->sum('saldo_akhir'), 2)
        ];
        $view->data = $data;
        return $view;
    }
    public function jurnal()
    {
        return view('main.jurnal');
    }
    public function pilihJurnal()
    {

        $view = view('main.pilih-jurnal');
        $view->books = BookJournal::get();
        return $view;
    }

    public function loginJurnal($id)
    {
        try {
            session()->put('book_journal_id', $id);
            return [
                'status' => 1,
                'msg' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function logoutJurnal()
    {
        session()->forget('book_journal_id');
        return redirect()->route('pilih.jurnal');
    }


    public function bukuBesar()
    {
        return view('main.buku-besar');
    }

    public function mutasi()
    {
        return view('main.mutasi');
    }

    public function getListBukuBesar()
    {
        $code = getInput('coa');
        $month = getInput('month');
        $year = getInput('year');
        $journals = Journal::searchCOA($code)->whereMonth('created_at', $month)->whereYear('created_at', $year)->orderBy('index_date', 'asc')->get();
        $chartAccount = ChartAccount::aktif()->pluck('name', 'code_group');
        return [
            'status' => 1,
            'msg' => $journals,
            'chart_accounts' => $chartAccount,
            'month'=> $month,
            'year'=> $year,
            'code_group'=> $code
        ];
    }


   
public static function createBaseJournal(Request $request, $useTransaction = true)
{
    $urlTryAgain = $request->input('url_try_again');
    $isBackDate = $request->input('is_backdate');
    $date = $isBackDate == 1 ? $request->input('date') : Date('Y-m-d H:i:s');
    $key = JournalKey::orderBy('id', 'desc')->first();
    if ($key && createCarbon($date) < $key->key_at) {
        JournalJobFailed::create(new Request([
            'type' => $request->input('title'),
            'request' => json_encode($request),
            'response' => 'pembuatan jurnal terblokir, karena jurnal sudat terkunci di ' . $key->key_at,
            'url_try_again' => $urlTryAgain
        ]));
        return [
            'status' => 0,
            'msg' => 'pembuatan jurnal tanggal segitu tidak bisa, karena sudah terkunci di ' . $key->key_at
        ];
    }

    $callback = function () use ($request, $urlTryAgain, $date, $isBackDate) {
        $kredits = $request->input('kredits');
        $debets = $request->input('debets');
        $type = $request->input('type');
        $tokoid = $request->input('toko_id');
        $isAuto = $request->input('is_auto_generated');
        $userBackdate = $request->input('user_backdate_id');

        if (collect($debets)->sum('amount') - collect($kredits)->sum('amount') != 0) {
            return [
                'status' => 0,
                'msg' => 'jumlah debet dan kredit berbeda'
            ];
        }

        $kodeType = match ($type) {
            'transaction' => 'JT',
            'keuangan' => 'JK',
            'purchasing' => 'JP',
            default => 'JU',
        };

        $tanggal = createCarbon($date)->format("ym");
        $kodeType .= ("-" . $tanggal);

        $lastJournalNumber = Journal::where('journal_number', 'like', $kodeType . '%')
            ->groupBy('journal_number')
            ->orderBy('journal_number', 'desc')
            ->first();

        $count = $lastJournalNumber ? intval(explode('-', $lastJournalNumber->journal_number)[2]) + 1 : 1;
        $theJournalNumber = sprintf("%s-%06d", $kodeType, $count);

        $allLocks = [];
        $allJournals = [];

        foreach ($debets as $debet) {
            self::addExpireTimeLocks($allLocks);
            $st = Journal::generateJournal(new Request([
                'journal_number' => $theJournalNumber,
                'code_group' => $debet['code_group'],
                'description' => $debet['description'],
                'amount_debet' => floatval($debet['amount']),
                'amount_kredit' => 0,
                'reference_id' => $debet['reference_id'],
                'reference_type' => $debet['reference_type'],
                'is_auto_generated' => $isAuto,
                'is_backdate' => $isBackDate,
                'toko_id' => $tokoid,
                'user_backdate_id' => $userBackdate,
                'date' => $date
            ]));
            $allLocks[] = ['lock' => $st['lock'], 'name' => $st['lock_name']];
            if ($st['status'] == 0) {
                self::releaseLocks($allLocks);
                JournalJobFailed::create(new Request([
                    'type' => $request->input('title'),
                    'request' => json_encode($request),
                    'response' => json_encode($st['msg']),
                    'url_try_again' => $urlTryAgain
                ]));
                return $st;
            }
            $allJournals[] = $st['msg'];
        }

        foreach ($kredits as $kredit) {
            self::addExpireTimeLocks($allLocks);
            $st = Journal::generateJournal(new Request([
                'journal_number' => $theJournalNumber,
                'code_group' => $kredit['code_group'],
                'description' => $kredit['description'],
                'amount_kredit' => floatval($kredit['amount']),
                'amount_debet' => 0,
                'reference_id' => $kredit['reference_id'],
                'reference_type' => $kredit['reference_type'],
                'is_auto_generated' => $isAuto,
                'is_backdate' => $isBackDate,
                'toko_id' => $tokoid,
                'user_backdate_id' => $userBackdate,
                'date' => $date
            ]));
            $allLocks[] = ['lock' => $st['lock'], 'name' => $st['lock_name']];
            if ($st['status'] == 0) {
                self::releaseLocks($allLocks);
                JournalJobFailed::create(new Request([
                    'type' => $request->input('title'),
                    'request' => json_encode($request),
                    'response' => json_encode($st['msg']),
                    'url_try_again' => $urlTryAgain
                ]));
                return $st;
            }
            $allJournals[] = $st['msg'];
        }

        DB::afterCommit(function () use ($allLocks, $allJournals, $theJournalNumber) {
            self::releaseLocks($allLocks);
            foreach ($allJournals as $thej) {
                $journal = Journal::find($thej->id);
                if ($journal->is_backdate == 1) {
                    RecalculateJournalJob::dispatch($journal->id);
                }
                $journal->updateLawanCode();
            }
        });

        return [
            'status' => 1,
            'msg' => 'success',
            'journal_number' => $theJournalNumber
        ];
    };

    try {
        return $useTransaction ? DB::transaction($callback) : $callback();
    } catch (\Throwable $e) {
        self::releaseLocks($allLocks ?? []);
        JournalJobFailed::create(new Request([
            'type' => $request->input('title'),
            'request' => json_encode($request),
            'response' => json_encode($e->getMessage()),
            'url_try_again' => $urlTryAgain
        ]));
        return [
            'status' => 0,
            'msg' => $e->getMessage(),
            'trace' => $e->getTrace(),
            'hal' => 'create base journal'
        ];
    }
}


    public static function addExpireTimeLocks($allLocks)
    {
        foreach ($allLocks as $datalock) {
            $name = $datalock['name'];
            Redis::expire($name, 50);
        }
    }

    public static function releaseLocks($allLocks)
    {
        foreach ($allLocks as $datalock) {
            if ($datalock['name'] != null) {
                $lock = $datalock['lock'];
                $lock->release();
                $name = $datalock['name'];
                // CustomLogger::log('journal', 'info', 'release lock ' . $name);
            }
        }
    }
}
