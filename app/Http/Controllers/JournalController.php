<?php

namespace App\Http\Controllers;

use App\Imports\MultiSheetImport;
use App\Jobs\ImportKartuStockJob;
use App\Jobs\ImportSaldoNLJob;
use App\Jobs\RecalculateJournalJob;
use App\Jobs\UpdateLawanCodeJournalJob;
use App\Models\BookJournal;
use App\Models\ChartAccount;
use App\Models\DetailKartuInvoice;
use App\Models\Journal;
use App\Models\JournalJobFailed;
use App\Models\JournalKey;
use App\Models\Stock;
use App\Models\TaskImport;
use App\Models\TaskImportDetail;
use App\Models\Toko;
use App\Services\LockManager;
use CustomLogger;
use Illuminate\Console\View\Components\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:59');
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
        $date = getInput('date') ?? null;
        if (!$date) {
            //breati ga ada permintaan date, kita cari di month dan year ya
            $month = getInput('month') ? toDigit(getInput('month'), 2) : Date('m');
            $year = getInput('year') ? getInput('year') : Date('Y');
            $date = createCarbon($year . '-' . $month . '-01')->format('Y-m-t 23:59:59');
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

        $view->books = BookJournal::where('type', '<>', 'own')->get();
        $view->thebook = $thebook;;
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
        $coas = ChartAccount::aktif()->where('code_group', 'like', Journal::getPrimaryCode($code) . '%')->pluck('code_group')->all();

        $subData = Journal::select(DB::raw('max(index_date) as maxindex'), 'code_group')->where('index_date', '<', $indexDate)->whereIn('code_group', $coas)
            ->groupBy('code_group');
        $lastSaldoJournal= Journal::joinSub($subData, 'sub_journals', function ($q) {
            $q->on('journals.index_date', '=', 'sub_journals.maxindex')
                ->on('journals.code_group', '=', 'sub_journals.code_group');
        })->pluck('journals.amount_saldo','journals.code_group')->all(); 
        $journals = Journal::searchCOA($code)->whereMonth('created_at', $month)->whereYear('created_at', $year)
            ->orderBy('index_date', 'asc')->get()->groupBy('code_group');
        $chartAccount = ChartAccount::aktif()->withAlias()->pluck('alias_name', 'code_group');
        return [
            'status' => 1,
            'msg' => $journals,
            'chart_accounts' => $chartAccount,
            'month' => $month,
            'year' => $year,
            'saldo_awal'=> $lastSaldoJournal,
            'code_group' => $code
        ];
    }



    public static function createBaseJournal(Request $request, $useTransaction = true, ?LockManager $lockManager = null)
    {
        $urlTryAgain = $request->input('url_try_again');
        $isBackDate = $request->input('is_backdate');
        $date = $isBackDate == 1 ? $request->input('date') : Date('Y-m-d H:i:s');
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
                    'tag' => array_key_exists('tag', $debet) ? $debet['tag'] : null
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
                    'tag' => array_key_exists('tag', $kredit) ? $kredit['tag'] : null

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
                if ($isBackDate == 1) {
                    $journal->recalculateJournal(false);
                }
            }



            if ($isLockIntern == 1) {
                //lock manual dilepas setelah semua proses transaksi selesai
                $lockManager->releaseAll();
            }
            return [
                'status' => 1,
                'msg' => 'success',
                'journal_number' => $theJournalNumber,
                'allLocks' => $allLocks
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

    function destroy($id)
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
                            $item->delete();
                            $details = DetailKartuInvoice::where('kartu_id', $itemID)->get();
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


        $import = new MultiSheetImport;
        Excel::import($import, $request->file('file'));

        // Ambil data yang sudah diproses
        $data = $import->data;
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
            $saldos = $data['saldo'];
            $stocks =  $data['stock'];
            $bookID = book()->id;
            $task = TaskImport::create([
                'book_journal_id' => $bookID,
                'type' => 'saldo_dan_stock_awal',
                'description' => 'import saldo awal ' . $date,

            ]);
            foreach ($saldos as $saldo) {

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
                ];
                $taskImportDetail = TaskImportDetail::create([
                    'task_import_id' => $task->id,
                    'type' => 'kartu_stock',
                    'payload' => json_encode($fixData),
                    'book_journal_id' => $bookID,
                ]);
                $taskKartuStock[] = $taskImportDetail->id;
            }
            DB::commit();
            // foreach ($taskSaldo as $saldo) {
            //     ImportSaldoNLJob::dispatch($saldo);
            // }
            // foreach ($taskKartuStock as $stock) {
            //     ImportKartuStockJob::dispatch($stock);
            // }
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
        if ($taskDetail->type == 'saldo_nl') {
            ImportSaldoNLJob::dispatch($id);
        } else if ($taskDetail->type == 'kartu_stock') {
            ImportKartuStockJob::dispatch($id);
        }
        return ['status' => 1, 'msg' => 'success'];
    }

    function resendImportTaskAll($id)
    {
        $task = TaskImport::find($id);
        $details = $task->details()->where('status', '<>', 'success')->select('id', 'type', 'status')->get();
        $antrianKartuStock = [];
        $antrianNL = [];
        $antrianMboh = [];
        foreach ($details as $detail) {
            try {
                if ($detail->type == 'saldo_nl') {
                    // ImportSaldoNLJob::dispatch($detail->id);
                    dispatch(new ImportSaldoNLJob($detail->id));
                    usleep(5000);
                    $antrianNL[] = $detail;
                } else if ($detail->type == 'kartu_stock') {
                    // ImportKartuStockJob::dispatch($detail->id);
                    dispatch(new ImportKartuStockJob($detail->id));
                    usleep(5000);

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

    public function getClosingJournal()
    {
        $date = getInput('date');
        $journal = Journal::where('tag', 'closing ' . $date)->first();
        $msg = $journal ? $journal->journal_number : null;
        return [
            'status' => 1,
            'msg' => $msg
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
                ->where('index_date', '<', (float)$theLastDate)->whereRaw('CONVERT(code_group, UNSIGNED) > ?', [400000])
                ->select('code_group', DB::raw('MAX(index_date) as max_index_date'))
                ->groupBy('code_group');

            $chartAccounts = DB::table('journals as j')
                ->joinSub($subquery, 'subquery', function ($join) {
                    $join->on('j.code_group', '=', 'subquery.code_group')
                        ->on('j.index_date', '=', 'subquery.max_index_date');
                })
                ->rightJoin('chart_accounts as ca', 'ca.id', '=', 'j.chart_account_id')
                ->where('ca.code_group', '>=', 400000)->where('ca.is_child', 1)
                ->select(
                    'ca.name',
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
                        'description' => 'penutupan ' . $ca->name . ' ' . $originalYear . '/' . $originalMonth,
                        'amount' => $saldo,
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                } elseif (bccomp($saldo, '0', 2) < 0) {
                    $kredits[] = [
                        'code_group' => $ca->code_group,
                        'description' => 'penutupan ' . $ca->name . ' ' . $originalYear . '/' . $originalMonth,
                        'amount' => bcmul($saldo, '-1', 2),
                        'reference_id' => null,
                        'reference_type' => null,
                        'tag' => $tag
                    ];
                }
            }

            $selisih = $totalSaldo;

            if (bccomp($selisih, '0', 2) > 0) {
                $kredits[] = [
                    'code_group' => 302200,
                    'description' => 'Ikhtisar laba rugi' . $originalYear . '/' . $originalMonth,
                    'amount' => $selisih,
                    'reference_id' => null,
                    'reference_type' => null,
                    'tag' => $tag
                ];
            } elseif (bccomp($selisih, '0', 2) < 0) {
                $debets[] = [
                    'code_group' => 302200,
                    'description' => 'Ikhtisar laba rugi' . $originalYear . '/' . $originalMonth,
                    'amount' => bcmul($selisih, '-1', 2),
                    'reference_id' => null,
                    'reference_type' => null,
                    'tag' => $tag
                ];
            }

            $allInput = [];
            $allOutput = [];
            $tanggalJurnal = createCarbon($monthyear)->addMonth()->format('Y-m-d 00:00:00');


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
            return ['status' => 1, 'input' => $allInput, 'output' => $allOutput];
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();
        }
        DB::rollBack();
        if ($lockManager) {
            $lockManager->releaseAll();
        }

        return ['status' => 0, 'msg' => $errMsg];
    }
}
