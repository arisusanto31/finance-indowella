<?php

namespace App\Http\Controllers;

use App\Imports\excel_kartu_stock\ExcelKartuStockImport;
use App\Imports\excel_saldo_awal_stock_jurnal\ExcelSaldoAwalImport;
use App\Imports\MultiSheetImport;
use App\Jobs\ImportKartuStockJob;
use App\Jobs\ImportSaldoNLJob;
use App\Jobs\RecalculateJournalJob;
use App\Jobs\UpdateLawanCodeJournalJob;
use App\Models\BookJournal;
use App\Models\BookTheme;
use App\Models\ChartAccount;
use App\Models\DetailKartuInvoice;
use App\Models\InvoiceSaleDetail;
use App\Models\Journal;
use App\Models\JournalJobFailed;
use App\Models\JournalKey;
use App\Models\KartuBahanJadi;
use App\Models\KartuBDP;
use App\Models\KartuDPSales;
use App\Models\KartuHutang;
use App\Models\KartuInventory;
use App\Models\KartuPiutang;
use App\Models\KartuPrepaidExpense;
use App\Models\KartuStock;
use App\Models\Stock;
use App\Models\TaskImport;
use App\Models\TaskImportDetail;
use App\Models\Toko;
use App\Services\LockManager;
use Carbon\Carbon;
use CustomLogger;
use Illuminate\Console\View\Components\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Throwable;

class JournalController extends Controller
{
    //
    public function neraca()
    {
        $view = view('main.neraca');

        $starttime = microtime(true);
        $date = getInput('date') ?? null;
        if (!$date) {
            //breati ga ada permintaan date, kita cari di month dan year ya
            $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
            $year = getInput('year') ? getInput('year') : Date('Y');
            $date = createCarbon($year . '-' . $month . '-01')->endOfMonth()->subSeconds(5)->format('Y-m-d H:i:s');
        }
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
        $view->month = $month;
        $view->year = $year;

        return $view;
    }

    public function neracalajur()
    {
        $view = view('main.neraca-lajur');
        $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('Y') : Date('Y');
        $view->data =  ChartAccount::getRincianSaldoNeracaLajur($month, $year);
        $view->month = $month;
        $view->year = $year;
        return $view;
    }
    public function getMutasiNeracaLajur()
    {
        $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $year = getInput('year') ? createCarbon(getInput('year') . '-01-01')->format('Y') : Date('Y');
        return ChartAccount::getRincianMutationNeracaLajur($month, $year);
    }

    public function linkJournal(Request $request)
    {
        $model = $request->input('model');
        $modelid = $request->input('model_id');
        $journal = Journal::find($request->input('journal_id'));
        $data = $model::find($modelid);
        $data->journal_id = $journal->id;
        $data->journal_number = $journal->journal_number;
        $data->save();
        $journal->verifyJournal();
        $journal->refresh();
        $data->refresh();
        return ['status' => 1, 'msg' => $data, 'journal' => $journal];
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
        $aliasChartAccount = ChartAccount::aktif()->withAlias()->pluck('alias_name', 'code_group')->all();
        $journal = Journal::whereIn('journal_number', $journalNumbers)->sortIndex()->get()->groupBy('journal_number')->map(function ($vals) use ($aliasChartAccount) {
            return collect($vals)->groupBy('code_group')->map(function ($v) use ($aliasChartAccount) {
                $data = collect($v)->first();
                if (collect($v)->count() > 1) {
                    $amountDebet = collect($v)->sum('amount_debet');
                    $amountKredit = collect($v)->sum('amount_kredit');
                    $selisih = $amountDebet - $amountKredit;
                    if ($selisih > 0) {
                        $data->amount_debet = $selisih;
                        $data->amount_kredit = 0;
                    } else if ($selisih <= 0) {
                        $data->amount_kredit = abs($selisih);
                        $data->amount_debet = 0;
                    }
                    $data->description = isset($aliasChartAccount[$data->code_group]) ? 'SUM ' . $aliasChartAccount[$data->code_group] : '';
                }
                return $data;
            })->values()->sortBy('amount_kredit')->values()->all();
        });
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

        $date = getInput('date') ?? null;
        if (!$date) {
            //breati ga ada permintaan date, kita cari di month dan year ya
            $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
            $year = getInput('year') ? getInput('year') : Date('Y');
            $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:54');
        }
        $labarugi = ChartAccount::getRincianLabaBulanAt($date);
        $data = [
            'status' => 1,
            'msg' => $labarugi,
            'laba_bulan' => round(collect($labarugi)->where('is_child', 1)->sum('saldo_akhir'), 2)
        ];
        $view->tokoes = Toko::all();
        $view->data = $data;
        $view->month = $month;
        $view->year = $year;
        return $view;
    }

    public function getLabaRugi($tokoid)
    {
        if ($tokoid == "" || $tokoid == "null" || $tokoid == 0) $tokoid = null;
        $date = getInput('date') ? getInput('date') : carbonDate();
          if (!$date) {
            //breati ga ada permintaan date, kita cari di month dan year ya
            $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
            $year = getInput('year') ? getInput('year') : Date('Y');
            $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:54');
        }
        $labarugi = ChartAccount::getRincianLabaBulanAt($date, $tokoid);
        return [
            'status' => 1,
            'msg' => $labarugi,
            'laba_bulan' => round(collect($labarugi)->where('is_child', 1)->sum('saldo_akhir'), 2)
        ];
    }


    public function jurnal()
    {
        return view('main.jurnal');
    }
    public function pilihJurnal()
    {

        $view = view('main.pilih-jurnal-bootstrap');
        $thebook = BookJournal::where('name', 'buku ' . user()->name)->first();
        if (!$thebook) {
            $thebook = BookJournal::create([
                'name' => 'buku ' . user()->name,
                'description' => 'buku jurnal ' . user()->name . ' bisa untuk coba coba ya ',
                'type' => 'own',
                'theme' => 'theme-default-brown.css'
            ]);
        }
        $bookTheme = BookTheme::where('user_id', user()->id)->pluck('theme', 'book_id')->all();
        $books = BookJournal::where('type', '<>', 'own')->get()->map(function ($val) use ($bookTheme) {
            $val->theme = $bookTheme[$val->id] ?? $val->theme;
            $val->theme_color = str_replace(['theme-default-', '.css'], '', $val->theme);
            return $val;
        });
        $thebook->theme = $bookTheme[$thebook->id] ?? $thebook->theme;
        $thebook->theme_color = str_replace(['theme-default-', '.css'], '', $thebook->theme);
        $view->books = $books;
        $view->thebook = $thebook;;
        return $view;
    }

    public function changeTheme(Request $request)
    {
        $bookid = $request->input('book_id');
        $theme = $request->input('theme');
        return BookTheme::createOrUpdate(user()->id, $bookid, $theme);
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
        $view = view('main.mutasi');
        $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
        $year = getInput('year') ?? Date('Y');
        $view->month = $month;
        $view->year = $year;
        return $view;
    }

    public function getListBukuBesar()
    {
        $code = getInput('coa');
        $month = getInput('month');
        $year = getInput('year');

        $indexDate = createCarbon($year . '-' . $month . '-01')->format('ymdHis00');

        $coas = ChartAccount::aktif()->child()->where('code_group', 'like', Journal::getPrimaryCode($code) . '%')->pluck('code_group')->all();
        $subData = Journal::select(DB::raw('max(index_date) as maxindex'), 'code_group')->where('index_date', '<', $indexDate)->whereIn('code_group', $coas)
            ->groupBy('code_group');
        $lastSaldoJournal = Journal::joinSub($subData, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.maxindex')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->pluck('journals.amount_saldo', 'journals.code_group')->all();

        $journals = Journal::searchCOA($code)->whereMonth('created_at', $month)->whereYear('created_at', $year)->with(['lawanCode:name,code_group'])
            ->orderBy('index_date', 'asc')->get()->groupBy('code_group');
        $chartAccount = ChartAccount::aktif()->withAlias()->pluck('alias_name', 'code_group');
        foreach ($coas as $coa) {
            if (!array_key_exists($coa, $journals->all())) {
                $journals[$coa] = [];
            }
        }
        return [
            'status' => 1,
            'msg' => $journals,
            'chart_accounts' => $chartAccount,
            'month' => $month,
            'year' => $year,
            'saldo_awal' => $lastSaldoJournal,
            'code_group' => $code
        ];
    }


    public function updateNotValid()
    {
        try {
            if (!getInput('code_group')) {
                throw new \Exception('code_group tidak boleh kosong');
            }
            $journals = Journal::where('code_group', getInput('code_group'))->whereNull('verified_by')->get();
            foreach ($journals as $journal) {
                $journal->verifyJournal();
            }
            return ['status' => 1, 'msg' => $journals->count()];
        } catch (\Exception $e) {
            return ['status' => 0, 'msg' => $e->getMessage()];
        }
    }


    function botFixJournal()
    {
        return view('main.bot_fix');
    }

    public static function cariProblemJournal()
    {
        $indexAwal = getInput('index_date') ? getInput('index_date') : 0;
        $indexAwal = intval(createCarbon($indexAwal)->format('ymdHis00'));
        $indexAkhir= Carbon::createFromFormat('ymdHis00', $indexAwal)->addMonths(12)->format('ymdHis00');
         
        //ari 3 bulan kedepan
        try {
            $journals = Journal::whereBetween('index_date', [$indexAwal, $indexAkhir])->select('index_date', 'journal_number', 'description', 'amount_kredit', 'amount_debet', 'code_group', 'amount_saldo', 'id')->orderBy('index_date', 'asc')->get();
            $sub = Journal::select(DB::raw('max(index_date) as max_index_date'), 'code_group')->where('index_date', '<', $indexAwal)->whereNotNull('amount_saldo')->groupBy('code_group');
            $lastJournal = Journal::joinSub($sub, 'last', function ($join) {
                $join->on('last.code_group', '=', 'journals.code_group')
                    ->on('last.max_index_date', '=', 'journals.index_date');
            })->select('journals.code_group', 'journals.index_date', 'journals.amount_debet', 'journals.amount_kredit', 'journals.amount_saldo', 'journals.journal_number', 'journals.id', 'journals.created_at')->get()->keyBy('code_group');

            foreach ($journals as $journal) {
                // $last = Journal::where('code_group', $journal->code_group)->where('index_date', '<', $journal->index_date)->orderBy('index_date', 'desc')->first();

                $last = $lastJournal[$journal->code_group] ?? null;
                if (!$last) {
                    $last =  new Journal;
                    $last->amount_saldo = 0;
                    $last->code_group = $journal->code_group;
                }
                if ($journal->code_group < 200000) { //aset
                    $mutasi = $journal->amount_debet - $journal->amount_kredit;
                } else {
                    $mutasi = $journal->amount_kredit - $journal->amount_debet;
                }
                $lastSaldo = $last ? $last->amount_saldo : 0;
                $saldo = floatval($journal->amount_saldo);
                $koreksi = floatval($lastSaldo + $mutasi);

                if (abs($saldo - $koreksi) > 0.001) {
                    return ['status' => 1, 'last' => $last, 'now' => $journal, 'perhitungan' => ($saldo - $koreksi)];
                }
                $lastJournal[$journal->code_group] = $journal; //update last journal
            }
            return [
                'status' => 0,
                'msg' => 'tidak ada problem',
                'from'=> $indexAwal,
                'to' => $indexAkhir
            ];
        } catch (Throwable $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function recalculateJournal($id)
    {
        $journal = Journal::find($id);
        if ($journal) {
            $journal->recalculateJournal();
        }
        return [
            'status' => 1,
            'journal' => $journal

        ];
    }

    public static function createBaseJournal(Request $request, $useTransaction = true, ?LockManager $lockManager = null)
    {
        $urlTryAgain = $request->input('url_try_again');

        $date = $request->input('date') ?? Date('Y-m-d H:i:s');
        //kalo lebih dari 30 menit yang lalu , brati backdate
        $dateDiff =  intdiv(carbonDate()->getTimestamp() - createCarbon($date)->getTimestamp(), 60);
        //apakah ini mewakili juga kalo ganti hari ?
        $isBackDate = $request->input('is_backdate') ?? null;
        if ($isBackDate === null)
            $isBackDate = $dateDiff > 30 ? 1 : 0;

        $key = JournalKey::orderBy('id', 'desc')->first();
        $isLockIntern = 0;
        if ($lockManager == null) {
            $isLockIntern = 1;
            $lockManager = new LockManager();
        }
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

        $callback = function () use ($request, $isLockIntern, $urlTryAgain, $date, $lockManager, $isBackDate, $useTransaction) {
            $kredits = $request->input('kredits');
            $debets = $request->input('debets');
            $type = $request->input('type');
            $tokoid = $request->input('toko_id');
            $isAuto = $request->input('is_auto_generated');
            $userBackdate = $request->input('user_backdate_id');

            if (abs(collect($debets)->sum('amount') - collect($kredits)->sum('amount')) > 0.001) {
                return [
                    'status' => 0,
                    'msg' => 'jumlah debet dan kredit berbeda',
                    'debets' => collect($debets)->sum('amount'),
                    'kredits' => collect($kredits)->sum('amount')
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
                    'toko_id' =>  array_key_exists('toko_id', $debet) ? $debet['toko_id'] : null,
                    'user_backdate_id' => $userBackdate,
                    'book_journal_id' => $request->input('book_journal_id'),
                    'date' => $date,
                    'tag' => array_key_exists('tag', $debet) ? $debet['tag'] : null,
                    'custom_amount_saldo' => array_key_exists('custom_amount_saldo', $debet) ? $debet['custom_amount_saldo'] : null
                ]), $lockManager);
                // $allLocks[] = ['lock' => $st['lock'], 'name' => $st['lock_name']];
                if ($st['status'] == 0) {
                    // self::releaseLocks($allLocks);
                    JournalJobFailed::create(new Request([
                        'type' => $request->input('title'),
                        'request' => json_encode($request),
                        'response' => json_encode($st['msg']),
                        'url_try_again' => $urlTryAgain
                    ]));
                    throw new \Exception($st['msg']);
                }
                $allJournals[] = $st['msg'];
            }

            foreach ($kredits as $kredit) {
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
                    'toko_id' => array_key_exists('toko_id', $kredit) ? $kredit['toko_id'] : null,
                    'user_backdate_id' => $userBackdate,
                    'date' => $date,
                    'book_journal_id' => $request->input('book_journal_id'),
                    'tag' => array_key_exists('tag', $kredit) ? $kredit['tag'] : null,
                    'custom_amount_saldo' => array_key_exists('custom_amount_saldo', $kredit) ? $kredit['custom_amount_saldo'] : null

                ]), $lockManager);
                // $allLocks[] = ['lock' => $st['lock'], 'name' => $st['lock_name']];

                if ($st['status'] == 0) {
                    // self::releaseLocks($allLocks);
                    JournalJobFailed::create(new Request([
                        'type' => $request->input('title'),
                        'request' => json_encode($request),
                        'response' => json_encode($st['msg']),
                        'url_try_again' => $urlTryAgain
                    ]));
                    throw new \Exception($st['msg']);
                }
                $allJournals[] = $st['msg'];
            }
            foreach ($allJournals as $journal) {
                $journal->updateLawanCode();
                $st = $journal->createDetailKartuInvoice();
                if ($st['status'] == 0) {
                    throw new \Exception('gagal membuat detail kartu invoice: ' . $st['msg']);
                }
                if ($isBackDate == 1) {
                    $journal->calculateJournalNext(false);
                }
            }
            if ($isLockIntern == 1) {
                //lock manual dilepas setelah semua proses transaksi selesai
                $lockManager->releaseAll();
            }
            return [
                'status' => 1,
                'msg' => 'success',
                'all_journals' => $allJournals,
                'journal_number' => $theJournalNumber,
                'allLocks' => $allLocks,
                'request' => $request->all()
            ];
        };

        try {
            return $useTransaction ? DB::transaction($callback) : $callback();
        } catch (\Throwable $e) {
            if ($isLockIntern) {
                $lockManager->releaseAll();
            }
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

    function searchError()
    {
        $codeGroup = getInput('code_group');
        $inputTanggal = getInput('daterange');
        $dateRange = explode(' - ', $inputTanggal);
        $startDate = $dateRange[0];
        $endDate = $dateRange[1];

        $description = getInput('description');
        $descriptions = explode(' ', $description);
        $query = Journal::query();
        if ($codeGroup) {
            $query->where('code_group', 'like', '%' . $codeGroup . '%');
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($description) {
            foreach ($descriptions as $desc) {
                $query->where('description', 'like', '%' . $desc . '%');
            }
        }
        $journals = $query->where('verified_by', null)->get();
        return [
            'status' => 1,
            'msg' => $journals
        ];
    }


    function verify($id)
    {
        $journal = Journal::find($id);
        $journal->verifyJournal();
        $journal->updateLawanCode();
        if ($journal->is_backdate == 1) {
            $journal->recalculateJournal();
        }
        return [
            'status' => 1,
            'msg' => 'success'
        ];
    }


    function previewDestroy($id)
    {
        $journalDeleted = [];
        $kartuDeleted = [];
        $linkDeleted = [];
        $journal = Journal::find($id);
        $key = JournalKey::orderBy('id', 'desc')->first();
        if ($key)
            if ($journal->created_at < $key->key_at) {
                return [
                    'status' => 0,
                    'msg' => 'jurnal sudah terkunci'
                ];
            }
        if (!user()->can('delete_data_journal')) {
            return [
                'status' => 0,
                'msg' => 'anda tidak memiliki hak akses untuk menghapus jurnal ini'
            ];
        }
        $journals = Journal::where('journals.journal_number', $journal->journal_number)->leftJoin('chart_accounts as ca', 'journals.code_group', '=', 'ca.code_group')
            ->select('journals.*', 'ca.name as coa_name')->get();

        // if (date_diff(createCarbon($journal->created_at), carbonDate())->days > 3) {
        //     //buat jurnal pembalikan balik aja 
        //     return self::cancelJournal($journal->journal_number);
        // } else {
        $lj = [];
        foreach ($journals as $j) {
            //kalo disini langsung hapus aja, biar lebih clean datanya
            $lj[] = Journal::where('code_group', $j->code_group)->where('index_date', '<', $j->index_date)->orderBy('index_date', 'desc')->first();
            if ($j->reference_model != null) {

                //menghapus relasi di model yang di link ke jurnal ini
                $model = $j->reference_model::where('journal_number', $j->journal_number)->get();
                if ($model->count() > 0) {


                    $model->each(function ($item) use (&$kartuDeleted, &$linkDeleted) {
                        $itemID = $item->id;
                        $class = get_class($item);
                        $item['class'] = $class;
                        $kartuDeleted[$item->id] = $item;
                        if ($itemID != null && $class != null) {
                            $details = DetailKartuInvoice::where('kartu_id', $itemID)->where('kartu_type', $class)->get();
                            foreach ($details as $detail) {
                                $linkDeleted[$detail->id] = $detail;
                            }
                        }
                    });
                }
            }
            $details = DetailKartuInvoice::where('journal_id', $j->id)->get();
            foreach ($details as $detail) {
                $linkDeleted[$detail->id] = $detail;
            }
            $journalDeleted[$j->id] = $j;
        }

        return [
            'status' => 1,
            'msg' => [
                'journals' => collect($journalDeleted)->values()->all(),
                'kartus' => collect($kartuDeleted)->values()->all(),
                'links' => collect($linkDeleted)->values()->all(),
                'recalculate_journals' => $lj
            ]
        ];
    }
    static function destroy($id)
    {

        DB::beginTransaction();
        try {
            $journal = Journal::find($id);
            $key = JournalKey::orderBy('id', 'desc')->first();
            if ($key)
                if ($journal->created_at < $key->key_at) {
                    return [
                        'status' => 0,
                        'msg' => 'jurnal sudah terkunci'
                    ];
                }
            if (!user()->can('delete_data_journal')) {
                return [
                    'status' => 0,
                    'msg' => 'anda tidak memiliki hak akses untuk menghapus jurnal ini'
                ];
            }
            $journals = Journal::where('journal_number', $journal->journal_number)->get();
            // if (date_diff(createCarbon($journal->created_at), carbonDate())->days > 3) {
            //     //buat jurnal pembalikan balik aja 
            //     return self::cancelJournal($journal->journal_number);
            // } else {
            $lj = [];
            foreach ($journals as $j) {
                //kalo disini langsung hapus aja, biar lebih clean datanya
                $lj[] = Journal::where('code_group', $j->code_group)->where('index_date', '<', $j->index_date)->orderBy('index_date', 'desc')->first();
                if ($j->reference_model != null) {
                    //menghapus relasi di model yang di link ke jurnal ini
                    $model = $j->reference_model::where('journal_number', $j->journal_number)->get();
                    if ($model->count() > 0) {
                        $model->each(function ($item) {
                            $itemID = $item->id;
                            $class = get_class($item);
                            $item->delete();
                            $details = DetailKartuInvoice::where('kartu_id', $itemID)->where('kartu_type', $class)->get();
                            foreach ($details as $detail) {
                                $detail->delete();
                            }
                        });
                    }
                }
                $details = DetailKartuInvoice::where('journal_id', $j->id)->get();
                foreach ($details as $detail) {

                    $detail->delete();
                }
                $j->delete();
            }
            foreach ($lj as $j) {
                if ($j)
                    $j->recalculateJournal();
            }
            // }

            DB::commit();
            return ['status' => 1, 'msg' => 'success'];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }


    public static function makeSaldoAwal($codes, $month = null, $year = null)
    {
        if ($month == null) {
            $month = getInput('month');
        }
        if ($year == null) {
            $year = getInput('year');
        }
        if ($month == null || $year == null) {
            return [
                'status' => 0,
                'msg' => 'bulan dan tahun harus diisi'
            ];
        }
        $starttime = microtime(true);
        $date = createCarbon($year . '-' . $month . '-01 00:00:00');
        //$codes berisi code group dan amount saldo

        $allca = ChartAccount::pluck('code_group')->all();

        foreach ($codes as $codegroup => $datacode) {

            if (!in_array($codegroup, $allca)) {
                //lek ga ada brati harus buat lo yaa..
                $st = ChartAccount::createNewChildChart(new Request([
                    'code_group' => $datacode['code_group'],
                    'name' => $datacode['name']
                ]));
                if ($st['status'] == 0) {
                    return $st;
                }
            }
        }

        $debets = [];
        $cas = ChartAccount::whereIn('code_group', collect($codes)->keys()->all())->get();


        foreach ($cas as $ca) {
            $debets[] = [
                'code_group' => $ca->code_group,
                'description' => 'rekap saldo awal ' . $ca->name . ' ' . $month . '/' . $year,
                'amount' => 0,
                'reference_id' => null,
                'reference_type' => null,
                'custom_amount_saldo' => $codes[$ca->code_group]['amount'],
                'tag' => 'opening ' . $month . '/' . $year
            ];
        }
        // return ['status' => 0, 'msg' => $debets];
        $st = JournalController::createBaseJournal(new Request([
            'debets' => $debets,
            'kredits' => [],
            'type' => 'umum',
            'user_backdate_id' => user()->id,
            'is_backdate' => 1,
            'date' => $date,
            'is_auto_generated' => 0,
            'title' => 'Jurnal tutup buku',
            'tag' => 'opening ' . $month . '/' . $year
        ]));

        if ($st['status'] == 0) {
            throw new \Exception($st['msg']);
        }
        return $st;
    }

    public static function cancelJournal($number)
    {
        $allJournals = Journal::where('journal_number', $number)->get();
        $debets = [];
        $kredits = [];
        $isBackdate = 0;
        $thedate = Date('Y-m-d H:i:s');
        foreach ($allJournals as $journal) {
            if ($journal->is_backdate == 1) {
                $isBackdate = 1;
                $thedate = createCarbon($journal->created_at)->addSeconds(5);
            }
            if ($journal->amount_debet > 0) {
                $kredits[] = [
                    'code_group' => $journal->code_group,
                    'description' => 'pembatalan ' . $journal->description,
                    'amount' => $journal->amount_debet,
                    'reference_id' => $journal->id,
                    'reference_type' => get_class($journal),
                ];
            } else if ($journal->amount_kredit > 0) {
                $debets[] = [
                    'code_group' => $journal->code_group,
                    'description' => 'pembatalan ' . $journal->description,
                    'amount' => $journal->amount_kredit,
                    'reference_id' => $journal->id,
                    'reference_type' => get_class($journal),
                ];
            }
        }

        $st = JournalController::createBaseJournal(new Request([
            'kredits' => $kredits,
            'debets' => $debets,
            'type' => 'umum',
            'is_backdate' => $isBackdate,
            'date' => $thedate,
            'is_auto_generated' => 1,
            'title' => 'pembatalan journal'
        ]), false);

        return $st;
    }

    public function recalculate($id)
    {
        $journal = Journal::find($id);
        if ($journal) {
            $journal->recalculateJournal();
            return [
                'status' => 1,
                'msg' => 'success'
            ];
        } else {
            return [
                'status' => 0,
                'msg' => 'jurnal tidak ditemukan'
            ];
        }
    }

    public static function getSaldoJournal($codeGroup)
    {
        $chart = ChartAccount::where('code_group', $codeGroup)->first();
        if (!$chart) {
            return [
                'status' => 0,
                'msg' => 'chart account tidak tersedia'
            ];
        }
        if (!getInput('date')) {
            return [
                'status' => 0,
                'msg' => 'tanggal tidak valid (' . getInput('date') . ')'
            ];
        }
        $saldo = $chart->getSaldoAt(getInput('date'));
        // $theindexdate = floatval(createCarbon(getInput('date'))->format('ymdHis') . '00');
        // $primaryCode = Journal::getPrimaryCode($chart->code_group);
        // $lastJournal = Journal::where('code_group', 'like', $primaryCode . '%')->where('index_date', '<=', $theindexdate)->orderBy('index_date', 'desc')->first();
        return [
            'status' => 1,
            'msg' => $saldo,
            // 'chart_account' => $chart,
            // 'journal' => $lastJournal
        ];
    }

    public function getSaldoHighlight()
    {
        $saldoPenjualan = self::getSaldoJournal(400000);
        $saldoHutang = self::getSaldoJournal(200000);
        $saldoPiutang = self::getSaldoJournal(120000);
        $saldoLaba = ChartAccount::getLabaBulanAt(getInput('date'));
        return [
            'status' => 1,
            'msg' => [
                'saldo_penjualan' => $saldoPenjualan,
                'saldo_hutang' => $saldoHutang,
                'saldo_piutang' => $saldoPiutang,
                'saldo_laba' => $saldoLaba
            ]
        ];
    }

    public function getSaldoCustom($codeGroup)
    {
        $saldo = self::getSaldoJournal($codeGroup);
        return [
            'status' => 1,
            'msg' => $saldo
        ];
    }


    public function getImportSaldo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);
        $date = $request->input('date');


        $import = new ExcelSaldoAwalImport;
        Excel::import($import, $request->file('file'));

        // Ambil data yang sudah diproses
        $data = $import->data;
        // return $data;
        $coas = ChartAccount::aktif()->pluck('name', 'code_group')->all();
        $stocks = Stock::pluck('name')->all();
        $stockRefs = Stock::whereNotNull('reference_stock_id')->pluck('reference_stock_id')->all();

        // Kirim ke view konfirmasi
        return view('main.import-saldo', compact('data', 'coas', 'stocks', 'date', 'stockRefs'));
    }

    public function importSaldo(Request $request)
    {

        DB::beginTransaction();
        try {
            $data = $request->input('data');
            $data = base64_decode($data);
            $date = $request->input('date');
            $data = json_decode($data, true);
            $taskSaldo = [];
            $taskKartuStock = [];
            $jurnals = $data['jurnal'];
            $stocks =  $data['stock'];
            $hutangs = $data['hutang'] ?? [];
            $inventaris = $data['inventaris'] ?? [];
            $bdds = $data['bdd'] ?? [];

            // proses hutang
            $bookID = book()->id;
            $task = TaskImport::create([
                'book_journal_id' => $bookID,
                'type' => 'saldo_dan_stock_awal',
                'description' => 'import saldo awal ' . $date,
                'request_date' => $date

            ]);
            foreach ($jurnals as $saldo) {

                $fixData = [
                    'code_group' => $saldo['code'],
                    'amount' => $saldo['amount'],
                    'date' => $date,
                    'name' => $saldo['name'],
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'book_journal_id' => $bookID,
                    'task_import_id' => $task->id,
                    'type' => 'saldo_nl',
                    'payload' => json_encode($fixData),
                    'request_date' => $date
                ]);
                $taskSaldo[] = $taskImportDetail->id;
                // ImportSaldoNLJob::dispatch($taskImportDetail->id);
            }
            foreach ($stocks as $stock) {
                $fixData = [
                    'name' => $stock['name'],
                    'amount' => $stock['amount'],
                    'quantity' => $stock['qty'],
                    'unit' => $stock['satuan'],
                    'ref_id' => $stock['ref_id'],
                    'date' => $date
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'task_import_id' => $task->id,
                    'type' => 'kartu_stock',
                    'payload' => json_encode($fixData),
                    'book_journal_id' => $bookID,
                    'request_date' => $date
                ]);
                $taskKartuStock[] = $taskImportDetail->id;
            }

            foreach ($hutangs as $hutang) {
                $fixData = [
                    'date' => excelSerialToCarbon($hutang['tanggal'])->format('Y-m-d'),
                    'supplier_name' => $hutang['supplier'],
                    'factur_supplier_number' => $hutang['no_invoice'],
                    'saldo_akhir' => $hutang['saldo_akhir'],
                    'factur_tax_number' => $hutang['no_faktur'],
                    'request_date' => $date,
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'task_import_id' => $task->id,
                    'type' => 'kartu_hutang',
                    'payload' => json_encode($fixData),
                    'book_journal_id' => $bookID,
                    'request_date' => $date
                ]);
            }

            foreach ($inventaris as $inv) {
                $fixData = [
                    'type_aset' => $inv['jenis'],
                    'name' => $inv['nama'],
                    'keterangan_qty_unit' => $inv['jumlah'],
                    'date' => excelSerialToCarbon($inv['tanggal'])->format('Y-m-d'),
                    'periode' => $inv['periode'],
                    'nilai_perolehan' => $inv['total_akumulasi'] + $inv['nilai_buku'],
                    'nilai_buku' => $inv['nilai_buku'],
                    'toko_id' => $inv['toko_id'],
                    'request_date' => $date
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'task_import_id' => $task->id,
                    'type' => 'kartu_inventaris',
                    'payload' => json_encode($fixData),
                    'book_journal_id' => $bookID,
                    'request_date' => $date
                ]);
            }

            foreach ($bdds as $bdd) {
                $fixData = [
                    'name'   => $bdd['keterangan'],
                    'periode' => $bdd['bulan'],
                    'nilai_perolehan' => $bdd['nilai'],
                    'nilai_buku'   => $bdd['saldo_akhir'],
                    'toko_id' => $bdd['toko_id'],
                    'date' => excelSerialToCarbon($bdd['tanggal'])->format('Y-m-d'),
                    'request_date' => $date
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'task_import_id' => $task->id,
                    'type' => 'kartu_prepaid',
                    'payload' => json_encode($fixData),
                    'book_journal_id' => $bookID,
                    'request_date' => $date
                ]);
            }

            DB::commit();

            return [
                'status' => 1,
                'msg' => $task
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }
    public function downloadTemplateSaldoAwal()
    {
        $fullPath = public_path("template/template_import_saldo_awal.xlsx");
        abort_unless(File::exists($fullPath), 404);
        return response()->download($fullPath);
    }



    function getImportSaldoFollowup($id)
    {
        $task = TaskImport::find($id);
        $details = $task->details->groupBy('type');
        $view = view('main.import-saldo-followup');
        $view->details = $details;
        $view->task = $task;
        return $view;
    }

    function getTaskImportAktif()
    {
        $taks = TaskImport::where('status', '<>', 'success')->with('details')->get()->map(function ($val) {
            $resume = collect($val->details)->groupBy('status')->map(function ($vals) {
                return $vals->count();
            });
            $resumeString = "";
            foreach ($resume as $key => $status) {
                $resumeString .= $key . " : " . $status . "x, ";
            }
            $val['resume_string'] = $resumeString;
            return $val;
        });
        return ['status' => 1, 'msg' => $taks];
    }


    function resendImportTask($id)
    {
        $taskDetail = TaskImportDetail::find($id);
        if ($taskDetail->type == 'kartu_stock')
            return KartuStockController::processTaskImport($id);
        else if ($taskDetail->type == 'kartu_hutang') {
            return KartuHutangController::processTaskImport($id);
        } else if ($taskDetail->type == 'kartu_inventaris') {
            return InventoryController::processTaskImport($id);
        } else if ($taskDetail->type == 'kartu_prepaid') {
            return BDDController::processTaskImport($id);
        }

        // $taskDetail = TaskImportDetail::find($id);
        // if ($taskDetail->type == 'kartu_stock') {
        //     ImportKartuStockJob::dispatch($id);
        // }
        return ['status' => 1, 'msg' => 'success'];
    }

    function resendImportTaskAll($id)
    {

        $details = TaskImportDetail::where('task_import_id', $id)->where('type', 'kartu_stock')->where('status', '<>', 'success')->select('id', 'type', 'status')->get();
        $antrianKartuStock = [];
        $antrianNL = [];
        $antrianMboh = [];
        foreach ($details as $row => $detail) {
            try {
                if ($detail->type == 'kartu_stock') {
                    // ImportKartuStockJob::dispatch($detail->id);
                    // dispatch(new ImportKartuStockJob($detail->id));
                    ImportKartuStockJob::dispatch($detail->id)->onQueue('default')->delay(now()->addMilliseconds(50 * $row));
                    $antrianKartuStock[] = $detail;
                } else {
                    $antrianMboh[] = $detail;
                }
            } catch (Throwable $e) {
                $antrianMboh[] = $detail;
            }
        }
        return [
            'status' => 1,
            'msg' => 'success',
            'details' => $details,
            'antrian_kartu_stock' => $antrianKartuStock,
            'antrian_nl' => $antrianNL,
            'antrian_mboh' => $antrianMboh
        ];
    }

    function sendImportTaskJurnal($id)
    {

        DB::beginTransaction();
        $taskDetail = TaskImportDetail::where('task_import_id', $id)->where('type', 'saldo_nl')->get()->map(function ($val) {
            $val['payload_array'] = json_decode($val->payload, true);
            return $val;
        });
        $allPayload = collect($taskDetail)->pluck('payload_array')->keyBy('code_group')->all();
        // return $allPayload [code:{name:'',amount:'',date:''}];
        $date = collect($taskDetail)->first()['payload_array']['date'];
        $st = self::makeSaldoAwal($allPayload, createCarbon($date)->format('m'), createCarbon($date)->format('Y'));

        if ($st['status'] == 1) {
            $number = $st['journal_number'];
            $journals = Journal::where('journal_number', $number)->pluck('amount_saldo', 'code_group')->all();
            $allPayload = collect($allPayload)->pluck('amount', 'code_group')->all();
            foreach ($allPayload as $codegroup => $amount) {
                $saldoJournal = array_key_exists($codegroup, $journals) ? $journals[$codegroup] : 0;
                if (bccomp((string)$amount, (string)$saldoJournal, 2) != 0) {
                    DB::rollBack();
                    return [
                        'status' => 0,
                        'msg' => 'saldo tidak sesuai untuk code group ' . $codegroup . ' , di jurnal : ' . $saldoJournal . ' , di import : ' . $amount
                    ];
                }
                $taskID = optional($taskDetail->where('type', 'saldo_nl')->where('payload_array.code_group', $codegroup)->first())->id ?? null;
                if ($taskID) {
                    $task = TaskImportDetail::find($taskID);
                    $task->status = 'success';
                    $task->save();
                    $payload = json_decode($task->payload, true);
                    ChartAccountController::makeAlias(new Request([
                        'code_group' => [$payload['code_group'], ""],
                        'name' => $payload['name'],
                        'no_update_ref' => true
                    ]));
                } else {
                    DB::rollBack();
                    return [
                        'status' => 0,
                        'msg' => 'task import untuk code group ' . $codegroup . ' tidak ditemukan'
                    ];
                }
            }
            DB::commit();
            return [
                'status' => 1,
                'msg' => 'success'
            ];
        } else {
            DB::rollBack();
            return $st;
        }
    }

    public function getClosingJournal()
    {
        $date = getInput('date');
        $journal = Journal::where('tag', 'closing ' . $date)->first();
        $msg = $journal ? $journal->journal_number : null;
        return [
            'status' => 1,
            'msg' => $msg,
            'monthyear' => $date
        ];
    }
    public static function tutupJurnal(Request $request)
    {

        $lockManager = new LockManager();
        DB::beginTransaction();
        $errMsg = "";
        try {
            $monthyear = $request->input('monthyear');
            $originalMonth = createCarbon($monthyear)->format('m');
            $originalYear = createCarbon($monthyear)->format('y');
            $originalYearLong = createCarbon($monthyear)->format('Y');
            $aksi = $request->input('aksi');
            $tag = 'closing ' . $monthyear;
            $theLastDate = createCarbon($monthyear)->addMonth()->format('ymdHis') . '00';
            $subquery = DB::table('journals')
                ->where('book_journal_id', book()->id)
                ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
                ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
                ->groupBy('code_group');

            $chartAccounts = DB::table('journals as j')
                ->joinSub($subquery, 'subquery', function ($join) {
                    $join->on('j.code_group', '=', 'subquery.code_group')
                        ->on('j.index_date', '=', 'subquery.max_index_date');
                })
                ->rightJoin('chart_accounts as ca', 'ca.code_group', '=', 'j.code_group')
                ->rightJoin('chart_account_aliases as alias',function($join){
                    $join->on('alias.code_group','=','ca.code_group')->where('alias.book_journal_id',book()->id);
                })
                ->where('ca.code_group', '>=', 400000)->where('ca.is_child', 1)
                ->select(
                    'alias.name',
                    'ca.id',
                    'ca.code_group',
                    DB::raw('CAST(ROUND(COALESCE(j.amount_saldo,0),2) AS DECIMAL(15,2)) AS saldo'),
                    'ca.is_child'
                )
                ->orderBy('ca.code_group')->get();

            $debets = [];
            $kredits = [];
            $totalSaldo = '0';

            foreach ($chartAccounts as $ca) {
                $saldo = number_format((string) $ca->saldo, 2, '.', '');
                $totalSaldo = bcadd($totalSaldo, $saldo, 2);
                if (bccomp($saldo, '0', 2) > 0) {
                    $debets[] = [
                        'code_group' => $ca->code_group,
                        'lawan_code_group' => 302200,
                        'description' => 'penutupan ' . $ca->name . ' ' . $originalYear . '/' . $originalMonth,
                        'amount' => $saldo,
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                    $kredits[] = [
                        'code_group' => 302200,
                        'lawan_code_group' => $ca->code_group,
                        'description' => 'Ikhtisar laba rugi (' . $ca->name . ')' . $originalYear . '/' . $originalMonth,
                        'amount' => $saldo,
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                } elseif (bccomp($saldo, '0', 2) < 0) {
                    $kredits[] = [
                        'code_group' => $ca->code_group,
                        'lawan_code_group' => 302200,
                        'description' => 'penutupan ' . $ca->name . ' ' . $originalYear . '/' . $originalMonth,
                        'amount' => round(abs($saldo), 2),
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                    $debets[] = [
                        'code_group' => 302200,
                        'lawan_code_group' => $ca->code_group,
                        'description' => 'Ikhtisar laba rugi (' . $ca->name . ')' . $originalYear . '/' . $originalMonth,
                        'amount' => round(abs($saldo), 2),
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                }
            }

            $selisih = $totalSaldo;

            // return [
            //     'debets'=> $debets,
            //     'kredits' => $kredits,
            // ];
            $allInput = [];
            $allOutput = [];
            $tanggalJurnal = createCarbon($monthyear)->endOfMonth()->format('Y-m-d H:i:s');
            if ($aksi == 1) {
                if (count($debets) > 0 && count($kredits) > 0) {
                    $st = JournalController::createBaseJournal(new Request([
                        'debets' => $debets,
                        'kredits' => $kredits,
                        'type' => 'umum',
                        'user_backdate_id' => user()->id,
                        'is_backdate' => 1,
                        'date' => $tanggalJurnal,
                        'is_auto_generated' => 0,
                        'title' => 'Jurnal tutup buku'
                    ]), false, $lockManager);

                    if ($st['status'] == 0) return $st;
                    $allOutput[] = $st;
                }
            }

            $allInput[] = [
                'debet' => $debets,
                'kredit' => $kredits
            ];

            $debets = [];
            $kredits = [];

            if (bccomp($selisih, '0', 2) > 0) {
                $codeDebet = 302200;
                $codeKredit = 302100;
            } else {
                $codeKredit = 302200;
                $codeDebet = 302100;
            }

            $selisihAbs = bccomp($selisih, '0', 2) < 0 ? bcmul($selisih, '-1', 2) : $selisih;

            $kredits[] = [
                'code_group' => $codeKredit,
                'description' => 'Pemindahan pada saldo laba ( ' . $originalYear . '/' . $originalMonth . ')',
                'amount' => $selisihAbs,
                'reference_id' => null,
                'reference_type' => null,
                'tag' => $tag
            ];

            $debets[] = [
                'code_group' => $codeDebet,
                'description' => 'Pemindahan pada saldo laba ( ' . $originalYear . '-' . $originalMonth . ')',
                'amount' => $selisihAbs,
                'reference_id' => null,
                'reference_type' => null,
                'tag' => $tag
            ];

            if ($aksi == 1) {
                $st = JournalController::createBaseJournal(new Request([
                    'debets' => $debets,
                    'kredits' => $kredits,
                    'type' => 'umum',
                    'user_backdate_id' => user()->id,
                    'is_backdate' => 1,
                    'date' => $tanggalJurnal,
                    'is_auto_generated' => 0,
                    'title' => 'Jurnal tutup buku'
                ]), false, $lockManager);

                if ($st['status'] == 1) {
                    JournalKey::create([
                        'book_journal_id' => bookID(),
                        'name' => 'kunci tutup buku ' . $tanggalJurnal,
                        'user_id' => user()->id,
                        'key_at' => createCarbon($tanggalJurnal),
                    ]);
                }
                $allOutput[] = $st;
            }

            $allInput[] = [
                'debet' => $debets,
                'kredit' => $kredits
            ];
            DB::commit();
            if ($lockManager) {
                $lockManager->releaseAll();
            }
            return ['status' => 1, 'input' => $allInput, 'output' => $allOutput, 'monthyear' => $monthyear];
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();
        }
        DB::rollBack();
        if ($lockManager) {
            $lockManager->releaseAll();
        }

        return ['status' => 0, 'msg' => $errMsg, 'input' => $allInput, 'monthyear' => $monthyear];
    }

    public static function hapusTutupJurnal(Request $request)
    {
        DB::beginTransaction();
        try {
            $monthyear = createCarbon($request->input('monthyear'))->startOfDay()->format('Y-m-d');


            $keyMonth = createCarbon($monthyear)->endOfMonth()->format('Y-m-d H:i:s');
            $journalKeys = JournalKey::where('key_at', '>', $keyMonth)->count();
            $nextKey= JournalKey::where('key_at', '>', $keyMonth)->orderBy('key_at', 'asc')->first();
            if ($journalKeys > 0) {
                return [
                    'status' => 0,
                    'msg' => 'penutup ' . $monthyear . ' terkunci oleh penutup bulan ' . createCarbon($nextKey->key_at)->format('Y-m-d'),
                    'monthyear' => $monthyear
                ];
            }
            $thejournalkey = JournalKey::where('key_at', $keyMonth)->first();
            $thejournalkey->delete();

            $tag = 'closing ' . $monthyear;
            $journal = Journal::where('tag', $tag)->first();
            if (!$journal) {
                return ['status' => 0, 'msg' => 'tidak ditemukan jurnal penutupan ' . $monthyear, 'monthyear' => $monthyear];
            }

            $journals = Journal::where('tag', $tag)->get();
            $lj = [];
            foreach ($journals as $j) {
                $lastJ = Journal::where('code_group', $j->code_group)->where('index_date', '<', $j->index_date)->orderBy('index_date', 'desc')->first();
                if ($lastJ) {
                    $lj[] = $lastJ;
                }
            }
          
            $dk = DetailKartuInvoice::whereIn('journal_id', $journals->pluck('id')->all())->delete();
            Journal::where('tag', $tag)->delete();
            //   throw new \Exception('sampek sini aman');
            foreach ($lj as $j) {
                if($j)
                $j->calculateJournalNext();
            }
            DB::commit();
            return ['status' => 1, 'msg' => 'success', 'monthyear' => $monthyear];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['status' => 0, 'msg' => $e->getMessage(), 'monthyear' => $monthyear];
        }
    }

    public function cariProblemKartu()
    {
        $model = getInput('model');
        $indexDate = getInput('index_date');
        $indexDate = createCarbon($indexDate)->format('ymdHis000');
        $stocks = Stock::pluck('name', 'id')->all();
        if ($model == 'KartuStock' || $model == 'KartuBDP' || $model == 'KartuBahanJadi') {
            $model = 'App\\Models\\' . $model;
            $datas = $model::where('index_date', '>', $indexDate)->select(
                'id',
                'stock_id',
                'index_date',
                'mutasi_qty_backend',
                'mutasi_rupiah_total',
                'saldo_qty_backend',
                'saldo_rupiah_total',
                'unit_backend',
                'production_number'
            )->orderBy('index_date', 'asc')->get()->groupBy('production_number')->map(function ($items) {
                return collect($items)->groupBy('stock_id');
            });
            $tableName = "kartu_stocks";
            switch ($model) {
                case 'App\\Models\\KartuStock':
                    $tableName = "kartu_stocks";
                    break;
                case 'App\\Models\\KartuBDP':
                    $tableName = "kartu_bdps";
                    break;
                case 'App\\Models\\KartuBahanJadi':
                    $tableName = "kartu_bahan_jadis";
                    break;
            }

            $saldoAwal = $model::whereIn('index_date', function ($q) use ($indexDate, $tableName) {
                $q->select(DB::raw('MAX(index_date)'))->from($tableName)->where('index_date', '<=', $indexDate)->groupBy('stock_id', 'production_number');
            })->get()->groupBy('production_number')->map(function ($items) {
                return collect($items)->keyBy('stock_id');
            });

            $problems = [];
            foreach ($datas as $prod => $vals) {
                foreach ($vals as $stockid => $valStocks) {
                    $saldoProd = $saldoAwal->get($prod);
                    $saldoQty = $saldoProd ? ($saldoProd->get($stockid)->saldo_qty_backend ?? 0) : 0;
                    $saldoRupiah = $saldoProd ? ($saldoProd->get($stockid)->saldo_rupiah_total ?? 0) : 0;
                    foreach ($valStocks as $row => $valStock) {
                        $saldoQty = bcadd($saldoQty, $valStock->mutasi_qty_backend, 2);
                        $saldoRupiah = bcadd($saldoRupiah, $valStock->mutasi_rupiah_total, 2);

                        if (abs($saldoQty - $valStock->saldo_qty_backend) > 0.1 || abs($saldoRupiah - $valStock->saldo_rupiah_total) > 0.1) {
                            $valStock['qty_ok'] = $saldoQty;
                            $valStock['rupiah_ok'] = $saldoRupiah;
                            $problems[] = $valStock;
                            break;
                        }
                    }
                }
            }

            return [
                'status' => 1,
                'msg' => $problems,
                'index_date' => $indexDate,
                'stocks' => $stocks,
                'model' => $model
            ];
        } else if ($model == 'KartuHutang' || $model == 'KartuPiutang' || $model == 'KartuDPSales') {
            $model = 'App\\Models\\' . $model;
            $key = 'sales_order_number';
            if ($model == 'App\\Models\\KartuHutang') {
                $key = 'factur_supplier_number';
            } else if ($model == 'App\\Models\\KartuPiutang') {
                $key = 'invoice_pack_number';
            }
            $datas = $model::where('index_date', '>', $indexDate)->select(
                'id',
                'index_date',
                DB::raw('amount_debet- amount_kredit as mutasi_rupiah_total'),
                DB::raw('amount_saldo_factur as saldo_rupiah_total'),
                DB::raw($key . ' as number')
            )->orderBy('index_date', 'asc')->get()->groupBy('number');
            $tableName = "kartu_hutangs";
            switch ($model) {
                case 'App\\Models\\KartuPiutang':
                    $tableName = "kartu_piutangs";
                    break;
                case 'App\\Models\\KartuDPSales':
                    $tableName = "kartu_dp_sales";
                    break;
                case 'App\\Models\\KartuHutang':
                    $tableName = "kartu_hutangs";
                    break;
            }

            $saldoAwal = $model::whereIn('index_date', function ($q) use ($indexDate, $tableName, $key) {
                $q->select(DB::raw('MAX(index_date)'))->from($tableName)->where('index_date', '<=', $indexDate)->groupBy($key);
            })->get()->keyBy($key);

            $problems = [];
            foreach ($datas as $number => $vals) {

                $saldoProd = $saldoAwal->get($number);
                $saldoRupiah = $saldoProd ? $saldoProd->amount_saldo_factur  : 0;
                foreach ($vals as $row => $val) {
                    $saldoRupiah = bcadd($saldoRupiah, $val->mutasi_rupiah_total, 2);

                    if (abs($saldoRupiah - $val->saldo_rupiah_total) > 0.1) {
                        $val['rupiah_ok'] = $saldoRupiah;
                        $problems[] = $val;
                        break;
                    }
                }
            }

            return [
                'status' => 1,
                'msg' => $problems,
                'index_date' => $indexDate,
                'model' => $model
            ];
        } else if ($model == 'KartuInventory' || $model == 'KartuPrepaidExpense') {
            $model = 'App\\Models\\' . $model;
            $key = 'sales_order_number';
            if ($model == 'App\\Models\\KartuInventory') {
                $key = 'inventory_id';
            } else if ($model == 'App\\Models\\KartuPrepaidExpense') {
                $key = 'prepaid_expense_id';
            }
            $datas = $model::where('index_date', '>', $indexDate)->select(
                'id',
                'index_date',
                DB::raw('amount as mutasi_rupiah_total'),
                DB::raw('nilai_buku as saldo_rupiah_total'),
                DB::raw($key . ' as number')
            )->orderBy('index_date', 'asc')->get()->groupBy('number');
            $tableName = "kartu_inventories";
            switch ($model) {
                case 'App\\Models\\KartuPrepaidExpense':
                    $tableName = "kartu_prepaid_expenses";
                    break;
            }   

            $saldoAwal = $model::whereIn('index_date', function ($q) use ($indexDate, $tableName, $key) {
                $q->select(DB::raw('MAX(index_date)'))->from($tableName)->where('index_date', '<=', $indexDate)->groupBy($key);
            })->get()->keyBy($key);

            $problems = [];
            foreach ($datas as $number => $vals) {

                $saldoProd = $saldoAwal->get($number);
                $saldoRupiah = $saldoProd ? $saldoProd->nilai_buku  : 0;
                foreach ($vals as $row => $val) {
                    $saldoRupiah = bcadd($saldoRupiah, $val->mutasi_rupiah_total, 2);

                    if (abs($saldoRupiah - $val->saldo_rupiah_total) > 0.1) {
                        $val['rupiah_ok'] = $saldoRupiah;
                        $problems[] = $val;
                        break;
                    }
                }
            }

            return [
                'status' => 1,
                'msg' => $problems,
                'index_date' => $indexDate,
                'model' => $model
            ];
        }

        return [
            'status' => 0,
            'msg' => 'model belum diakomodasi',
            'model' => $model
        ];
    }

    public function fixProblemKartu(Request $request)
    {

        try {
            $id = $request->input('id');
            $model = $request->input('model');

            if ($model == 'KartuStock' || $model == 'KartuBDP' || $model == 'KartuBahanJadi') {
                $model = 'App\\Models\\' . $model;
                $kartu = $model::find($id);
                if (!$kartu) {
                    throw new \Exception('kartu tidak ditemukan');
                }
                $lastKartu = $model::where('production_number', $kartu->production_number)
                    ->where('stock_id', $kartu->stock_id)
                    ->where('index_date', '<', $kartu->index_date)->orderBy('index_date', 'desc')
                    ->first();

                $saldoQty = $lastKartu ? $lastKartu->saldo_qty_backend : 0;
                $saldoRupiah = $lastKartu ? $lastKartu->saldo_rupiah_total : 0;
                $nextKartu = $model::where('production_number', $kartu->production_number)
                    ->where('stock_id', $kartu->stock_id)
                    ->where('index_date', '>=', $kartu->index_date)->orderBy('index_date', 'asc')
                    ->get();

                $updater = [];
                foreach ($nextKartu as $k) {
                    $saldoQty = bcadd($saldoQty, $k->mutasi_qty_backend, 2);
                    $saldoRupiah = bcadd($saldoRupiah, $k->mutasi_rupiah_total, 2);
                    $k->saldo_qty_backend = $saldoQty;
                    $k->saldo_rupiah_total = $saldoRupiah;
                    $updater[] = [
                        'id' => $k->id,
                        'saldo_qty_backend' => $saldoQty,
                        'saldo_rupiah_total' => $saldoRupiah
                    ];
                }
                upsertInChunks($model, $updater, 'id', ['saldo_qty_backend', 'saldo_rupiah_total']);
            } else if ($model == 'KartuHutang' || $model == 'KartuPiutang' || $model == 'KartuDPSales') {
                $model = 'App\\Models\\' . $model;
                $kartu = $model::find($id);
                if (!$kartu) {
                    throw new \Exception('kartu tidak ditemukan');
                }
                $key = 'sales_order_number';
                if ($model == 'App\\Models\\KartuHutang') {
                    $key = 'factur_supplier_number';
                } else if ($model == 'App\\Models\\KartuPiutang') {
                    $key = 'invoice_pack_number';
                }
                $lastKartu = $model::where($key, $kartu->$key)
                    ->where('index_date', '<', $kartu->index_date)->orderBy('index_date', 'desc')
                    ->first();

                $saldoRupiah = $lastKartu ? $lastKartu->amount_saldo_factur  : 0;
                $nextKartu = $model::where($key, $kartu->$key)
                    ->where('index_date', '>=', $kartu->index_date)->orderBy('index_date', 'asc')
                    ->get();

                $updater = [];
                foreach ($nextKartu as $k) {
                    $saldoRupiah = bcadd($saldoRupiah, bcsub($k->amount_debet, $k->amount_kredit, 2), 2);
                    $k->amount_saldo_factur = $saldoRupiah;
                    $updater[] = [
                        'id' => $k->id,
                        'amount_saldo_factur' => $saldoRupiah
                    ];
                }
                upsertInChunks($model, $updater, 'id', ['amount_saldo_factur']);
            }
            else if($model=='KartuInventory' || $model == 'KartuPrepaidExpense'){
                $model = 'App\\Models\\' . $model;
                $kartu = $model::find($id);
                if (!$kartu) {
                    throw new \Exception('kartu tidak ditemukan');
                }
                $key = 'inventory_id';
                if ($model == 'App\\Models\\KartuPrepaidExpense') {
                    $key = 'prepaid_expense_id';
                }
                $lastKartu = $model::where($key, $kartu->$key)
                    ->where('index_date', '<', $kartu->index_date)->orderBy('index_date', 'desc')
                    ->first();

                $saldoRupiah = $lastKartu ? $lastKartu->nilai_buku  : 0;
                $nextKartu = $model::where($key, $kartu->$key)
                    ->where('index_date', '>=', $kartu->index_date)->orderBy('index_date', 'asc')
                    ->get();

                $updater = [];
                foreach ($nextKartu as $k) {
                    $saldoRupiah = bcadd($saldoRupiah, $k->amount, 2);
                    $k->nilai_buku = $saldoRupiah;
                    $updater[] = [
                        'id' => $k->id,
                        'nilai_buku' => $saldoRupiah
                    ];
                }
                upsertInChunks($model, $updater, 'id', ['nilai_buku']);
            }
            else {
                throw new \Exception('model belum diakomodasi');
            }
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
        return [
            'status' => 1,
            'msg' => 'success'
        ];
    }

    function detailPencocokan()
    {
        $date = getInput('date');
        $dateRange = getInput('date_range');
        $model = getInput('model');


        if ($dateRange) {
            $splitDate = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('d/m/Y', $splitDate[0])->format('Y-m-d 00:00:00');
            $endDate = Carbon::createFromFormat('d/m/Y', $splitDate[1])->format('Y-m-d 23:59:59');
            $indexStart = Carbon::createFromFormat('d/m/Y', $splitDate[0])->format('ymd00000000');
            $indexEnd = Carbon::createFromFormat('d/m/Y', $splitDate[1])->format('ymd23595999');
        } else {

            $startDate = createCarbon($date)->format('Y-m-d 00:00:00');
            $endDate = Carbon::now()->format('Y-m-d 23:59:59');
            $indexStart = createCarbon($date)->format('ymdHis00');
            $indexEnd = Carbon::now()->format('ymd23595999');
        }
        $fixModel = 'App\\Models\\' . $model;
        // $journals = Journal::where('index_date', '>=', $indexDateJournal)
        //     ->select('index_date', 'description', 'amount_debet', 'amount_kredit')->get();
        $tableName = "";
        if ($model == 'KartuStock') {
            $tableName = "kartu_stocks";
            $saldoKartu = KartuStock::getTotalSaldoRupiah($startDate, true);
            $saldoJournal = KartuStock::getTotalJournal($startDate);
        } else if ($model == 'KartuBDP') {
            $tableName = "kartu_bdps";
            $saldoKartu = KartuBDP::getTotalSaldoRupiah($startDate, true);
            $saldoJournal = KartuBDP::getTotalJournal($startDate);
        } else if ($model == 'KartuBahanJadi') {
            $tableName = "kartu_bahan_jadis";
            $saldoKartu = KartuBahanJadi::getTotalSaldoRupiah($startDate, true);
            $saldoJournal = KartuBahanJadi::getTotalJournal($startDate);
        } else if ($model == 'KartuHutang') {
            $tableName = "kartu_hutangs";

            $saldoKartu = KartuHutang::getTotalsaldoRupiah($startDate, 'factur_supplier_number');
            $saldoJournal = KartuHutang::getTotalJournal($startDate);
        } else if ($model == 'KartuPiutang') {
            $tableName = "kartu_piutangs";
            $saldoKartu = KartuPiutang::getTotalSaldoRupiah($startDate);
            $saldoJournal = KartuPiutang::getTotalJournal($startDate);
        } else if ($model == 'KartuDPSales') {
            $tableName = "kartu_dp_sales";
            $saldoKartu = KartuDPSales::getTotalSaldoRupiah($startDate, 'sales_order_number');
            $saldoJournal = KartuDPSales::getTotalJournal($startDate);
        } else if ($model == 'KartuInventory') {
            $tableName = "kartu_inventories";
            $saldoKartu = KartuInventory::getTotalSaldoRupiah($startDate, 'inventory_id');
            $saldoJournal = KartuInventory::getTotalJournal($startDate);
        } else if ($model == 'KartuBDD') {
            $tableName = "kartu_prepaid_expenses";
            $saldoKartu = KartuPrepaidExpense::getTotalSaldoRupiah($startDate, 'prepaid_expense_id');
            $saldoJournal = KartuPrepaidExpense::getTotalJournal($startDate);
        } else {
            return [
                'status' => 0,
                'msg' => 'model belum diakomodasi'
            ];
        }

        if ($model == "KartuStock" || $model == "KartuBDP" || $model == "KartuBahanJadi") {
            $kartu = $fixModel::from($tableName . ' as kartu')->leftJoin('detail_kartu_invoices as dk', function ($join) use ($fixModel) {
                $join->on('dk.kartu_id', '=', 'kartu.id')->where('dk.kartu_type', $fixModel);
            })
                ->where('kartu.index_date', '>', $indexStart . '0')
                ->where('kartu.index_date', '<=', $indexEnd . '9')
                ->select(
                    'kartu.id',
                    'kartu.index_date',
                    'kartu.stock_id',
                    'kartu.mutasi_rupiah_total as amount',
                    DB::raw('coalesce(dk.journal_id, kartu.journal_id) as journal_id'),
                )->groupBy('kartu.id')->get();
        } else if ($model == "KartuHutang" || $model == "KartuPiutang" || $model == "KartuDPSales") {
            $kartu = $fixModel::from($tableName . ' as kartu')
                ->leftJoin('detail_kartu_invoices as dk', function ($join) use ($fixModel) {
                    $join->on('dk.kartu_id', '=', 'kartu.id')->where('dk.kartu_type', $fixModel);
                })
                ->where('kartu.index_date', '>', $indexStart . '0')
                ->where('kartu.index_date', '<=', $indexEnd . '9')
                ->select(
                    'kartu.id',
                    'kartu.index_date',
                    DB::raw('kartu.amount_debet - kartu.amount_kredit as amount'),
                    'kartu.description',
                    DB::raw('coalesce(dk.journal_id, kartu.journal_id) as journal_id'),
                )->groupBy('kartu.id')->get();
        } else if ($model == 'KartuBDD' || $model == 'KartuInventaris') {
            $kartu = $fixModel::from($tableName . ' as kartu')
                ->leftJoin('detail_kartu_invoices as dk', function ($join) use ($fixModel) {
                    $join->on('dk.kartu_id', '=', 'kartu.id')->where('dk.kartu_type', $fixModel);
                })
                ->where('kartu.index_date', '>', $indexStart . '0')
                ->where('kartu.index_date', '<=', $indexEnd . '9')
                ->select(
                    'kartu.index_date',
                    'kartu.description',
                    'kartu.amount',
                    'kartu.type_mutasi',
                    'kartu.nilai_buku',
                    DB::raw('coalesce(dk.journal_id, kartu.journal_id) as journal_id'),
                )->groupBy('kartu.id')->get();
        } else {
            return [
                'status' => 0,
                'msg' => 'model belum diakomodasi'
            ];
        }

        $journals = Journal::from('journals as j')
            ->leftJoin('detail_kartu_invoices as dk', function ($join) use ($fixModel) {
                $join->on('dk.journal_id', '=', 'j.id')->where('dk.kartu_type', $fixModel);
            })
            ->leftJoin('chart_accounts as ca', 'ca.code_group', '=', 'j.code_group')
            ->where('j.index_date', '>', $indexStart)
            ->where('j.index_date', '<=', $indexEnd)
            ->where('j.reference_model', $fixModel)
            ->select(
                'j.id',
                'j.index_date',
                'ca.name as code_group_name',
                'j.journal_number',
                'j.description',
                'j.code_group',
                DB::raw('group_concat(dk.kartu_id) as kartu_id'),
                DB::raw('case when j.code_group > 200000 then j.amount_kredit-j.amount_debet else j.amount_debet-j.amount_kredit end as amount')
            )->groupBy('j.id')->orderBy('j.index_date', 'asc')->get();

        $view = view('main.detail-pencocokan');
        $view->kartus = $kartu;
        $view->journals = $journals;
        $view->model = $model;
        $view->lastSaldoKartu = $saldoKartu;
        $view->lastSaldoJournal = $saldoJournal;
        $view->startDate = $startDate;
        $view->endDate = $endDate;
        $view->indexStart = $indexStart;
        $view->indexEnd = $indexEnd;
        return $view;
    }
}
