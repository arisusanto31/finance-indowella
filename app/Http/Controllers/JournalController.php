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
            'month' => $month,
            'year' => $year,
            'code_group' => $code
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
            if (date_diff(createCarbon($journal->created_at), carbonDate())->days > 3) {
                //buat jurnal pembalikan balik aja 
                return self::cancelJournal($journal->journal_number);
            } else {
                $lj = [];
                foreach ($journals as $j) {
                    //kalo disini langsung hapus aja, biar lebih clean datanya
                    $lj[] = Journal::where('code_group', $j->code_group)->where('index_date', '<', $j->index_date)->orderBy('index_date', 'desc')->first();
                    if ($j->reference_model != null) {
                        //menghapus relasi di model yang di link ke jurnal ini
                        $model = $j->reference_model::where('journal_id', $j->id)->get();
                        if ($model->count() > 0) {
                            $model->each(function ($item) {
                                $item->journal_id = null;
                                $item->journal_number = null;
                                $item->save();
                            });
                        }
                    }
                    $j->delete();
                }
                foreach ($lj as $j) {
                    if ($j)
                        $j->recalculateJournal();
                }
            }

            DB::commit();
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
}
