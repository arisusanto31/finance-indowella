<?php

namespace App\Models;

use App\Jobs\CreateKartuUtangJob;
use App\Services\LockManager;
use CustomLogger;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;
use Illuminate\Support\Str;

class Journal extends Model
{
    use HasFactory;
    protected $table = "journals";


    public function reference()
    {
        return $this->morphTo();
    }

    public function codeGroupData()
    {
        return $this->belongsTo(ChartAccount::class, 'code_group', 'code_group');
    }
    public function codeGroupLawanData()
    {
        return $this->belongsTo(ChartAccount::class, 'lawan_code_group', 'code_group');
    }



    protected static function booted()
    {

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'journals'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", bookID());
            });
        });
    }

    public function chartAccount()
    {
        return $this->belongsTo('App\Models\ChartAccount', 'chart_account_id');
    }
    public static function createOrUpdate(Request $request)
    {
        $id = $request->input('id');

        try {
            $journal = $id ? Journal::find($id) : new Journal;
            $journal->chart_account_id = $request->input('chart_account_id');
            $journal->journal_number = $request->input('journal_number');
            $journal->description = $request->input('description');
            $journal->amount_debet = $request->input('amount_debet');
            $journal->amount_kredit = $request->input('amount_kredit');
            $journal->code_group = $request->input('code_group');


            $journal->reference_id = $request->input('reference_id');
            $journal->reference_type = $request->input('reference_type');
            $journal->verified_by = $request->input('verified_by');
            $journal->is_auto_generated = $request->input('is_auto_generated');
            $journal->save();
            return [
                'status' => 1,
                'msg' => $journal
            ];
        } catch (\Exception $e) {
            return [
                'status' => 0,
                'msg' => $e->getMessage()
            ];
        }
    }

    public function updateLawanCode()
    {
        if (!$this->lawan_code_group) {
            $lawanJournal = Journal::where('journal_number', $this->journal_number)->whereRaw('abs(amount_debet+ amount_kredit)=?', [abs($this->amount_debet + $this->amount_kredit)])->where('id', '<>', $this->id)->first();
            if ($lawanJournal) {
                $this->lawan_code_group = $lawanJournal->code_group;
                $this->save();
                $lawanJournal->lawan_code_group = $this->code_group;
                $lawanJournal->save();
            }
        }
    }
    // 'reference_id' => $transaction->id,
    // 'reference_type' => get_class($transaction),
    public static function generateJournal(Request $request, ?LockManager $lockManager = null)
    {
        $debet = $request->input('amount_debet');
        $kredit = $request->input('amount_kredit');
        $codeGroup = $request->input('code_group');
        $isBackDate = $request->input('is_backdate');
        $tokoID = $request->input('toko_id');
        if (!$tokoID) {
            $tokoID = Toko::first()->id;
        }
        if ($codeGroup > 400000) {
            if (!$tokoID) {
                $msg = 'toko_id tidak boleh kosong untuk membuat jurnal ' . $codeGroup . ' ini' . json_encode($request->all());
                throw new \Exception($msg);
            }
        }
        $chartAccount = ChartAccount::where('code_group', $codeGroup)->first();
        if (!$chartAccount) {
            return [
                'status' => 0,
                'msg' => 'tidak ditemukan chart account ' . $codeGroup,
                'lock' => null,
                'lock_name' => null
            ];
        }
        $coaID = $chartAccount->id;
        $journal_number = $request->input('journal_number');
        $name = 'generate-journal' . $codeGroup;


        // CustomLogger::log('journal', 'info', $journal_number . ' make lock ' . $name);
        try {
            $lockManager->acquire($name, 50, 20);
            Redis::expire($name, 50);
            try {
                // CustomLogger::log('journal', 'info', $journal_number . ' get lock ' . $name);
                $now = createCarbon($request->input('date'));
                $counter = 9999;
                while ($counter >= 99) {
                    $indexDate = $now->format('ymdHis');
                    info('code Group:' . $coaID . ' on ' . $indexDate . ',bookid=' . bookID());
                    $lastIndexDate = Journal::where('chart_account_id', $coaID)->where('index_date_group', $indexDate)->select(DB::raw('max(index_date) as maxindex'))->first();
                    $counter = $lastIndexDate ? $lastIndexDate->maxindex % 100 : 0;
                    if ($counter >= 99) {
                        $now->addSecond();
                    }
                }
                info('counter:' . $counter);
                $finalIndexDate = $indexDate . sprintf("%02d", ($counter + 1));
                $lastJournal = Journal::where('chart_account_id', $coaID)->where('index_date', '<', $finalIndexDate)->orderBy('index_date', 'desc')->first();

                $journal = new Journal;
                $chartAccount = ChartAccount::find($coaID);
                $journal->index_date = $finalIndexDate;
                $journal->index_date_group = $indexDate; //nilai ymdHis
                $journal->chart_account_id = $coaID;
                $journal->reference_model = $chartAccount->reference_model;
                $journal->journal_number = $journal_number;
                $journal->code_group = $request->input('code_group');
                $journal->description = $request->input('description');
                $journal->amount_debet = $debet > 0 ? $debet : 0;
                $journal->amount_kredit = $kredit > 0 ? $kredit : 0;
                if ($chartAccount->account_type == 'Aset') {
                    $theAmount = $journal->amount_debet - $journal->amount_kredit;
                    // info($codeGroup . '-aktiva');
                    // info($theAmount);
                } else {
                    $theAmount = $journal->amount_kredit - $journal->amount_debet;
                    // info($codeGroup . '-passiva');
                    // info($theAmount);
                }
                $journal->reference_id = $request->input('reference_id');
                $journal->reference_type = $request->input('reference_type');
                $lastSaldo = $lastJournal ? $lastJournal->amount_saldo : 0;
                $journal->amount_saldo = round($lastSaldo + $theAmount, 2);
                $journal->is_backdate = $isBackDate;
                $journal->user_backdate_id = $request->input('user_backdate_id');
                $journal->toko_id = $request->input('toko_id');
                $journal->created_at = $now->format('Y-m-d H:i:s');
                $journal->is_auto_generated = $request->input('is_auto_generated');
                $journal->book_journal_id = bookID() ?? $request->Input('book_journal_id');
                $journal->toko_id = $request->input('toko_id');
                $journal->save();
                $reference = null;
                if ($journal->reference_type != null)
                    $reference = $journal->reference;


                if ($reference) {
                    if (get_class($reference) != 'App\Models\\Journal') {
                        $reference->journal_number = $journal->journal_number;
                        $reference->save();
                        if (get_class($reference) == 'App\Models\PembayaranPiutang') {
                            foreach ($reference->details as $detail) {
                                $detail->journal_number = $reference->journal_number;
                                $detail->save();
                            }
                        }
                    }
                }
                info('success creating journal' . $codeGroup);
                $journal->verifyJournal();
            } catch (Throwable $e) {
                info('failed creating journal: ' . $e->getMessage());
                return [
                    'status' => 0,
                    'msg' => $e->getMessage(),
                    // 'lock' => $lock,
                    // 'lock_name' => $name
                ];
            }
        } catch (LockTimeoutException $e) {
            info('failed creating journal: antrian timeout');
            return [
                'status' => 0,
                'msg' => 'jurnal tidak berhasil masuk, antrian timeout',
                // 'lock' => $lock,
                // 'lock_name' => $name
            ];
        } finally {
            // $lock->release();
        }
        return [
            'status' => 1,
            'msg' => $journal,
            // 'lock' => $lock,
            // 'lock_name' => $name,

        ];
    }

    public function verifyJournal()
    {
        $this->refresh();
        if ($this->reference_model) {
            $this->verified_by = null;

            if ($this->reference_model == 'App\Models\KartuStock') {
                $ks = KartuStock::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('mutasi_rupiah_total')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('mutasi_rupiah_total')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            }
            if ($this->reference_model == 'App\Models\KartuBDP') {
                $ks = KartuBDP::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('mutasi_rupiah_total')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('mutasi_rupiah_total')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            }
            if ($this->reference_model == 'App\Models\KartuBahanJadi') {
                $ks = KartuBahanJadi::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('mutasi_rupiah_total')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('mutasi_rupiah_total')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\KartuHutang') {
                $ks = KartuHutang::where('journal_id', $this->id)->get();
                $totalAmount = abs($ks->sum('amount_debet') - $ks->sum('amount_kredit'));
                info(abs($totalAmount) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (($totalAmount) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\KartuPiutang') {
                $ks = KartuPiutang::where('journal_id', $this->id)->get();
                $totalAmount = abs($ks->sum('amount_debet') - $ks->sum('amount_kredit'));
                info(abs($totalAmount) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if ($totalAmount == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\KartuInventory') {
                $ks = KartuInventory::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('amount')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('amount')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\KartuPrepaidExpense') {
                $ks = KartuPrepaidExpense::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('amount')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('amount')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\KartuDPSales') {
                $ks = KartuDPSales::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('amount_debet') - collect($ks)->sum('amount_kredit')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('amount_debet') - collect($ks)->sum('amount_kredit')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            } else if ($this->reference_model == 'App\Models\InvoiceSaleDetail') {
                $ks = InvoiceSaleDetail::where('journal_id', $this->id)->get();
                info(abs(collect($ks)->sum('total')) . '==' . abs($this->amount_debet - $this->amount_kredit));
                if (abs(collect($ks)->sum('total')) == abs($this->amount_debet - $this->amount_kredit)) {
                    $this->verified_by = 1;
                }
            }
        } else {
            $this->verified_by = 1;
        }
        $this->save();
        return $this;
    }

    public function recalculateJournal($isLock = true)
    {
        $thejournal = $this;
        $codeGroup = $this->code_group;
        $name = 'generate-journal' . $codeGroup;
        if ($isLock == true) {
            $lock = Cache::lock($name, 120);
        }
        // CustomLogger::log('journal', 'info', 'recalculate make lock ' . $name);
        try {
            if ($isLock == true) {
                $lock->block(20);
            }
            $mustEditJournal = Journal::where('index_date', '>', $thejournal->index_date)->where('code_group', $thejournal->code_group)->sortindex()->get();
            $lastSaldo = $thejournal->amount_saldo;
            $newdata = [];
            foreach ($mustEditJournal as $journal) {

                if ($journal->code_group < 200000) { //aktiva
                    $journal->amount_saldo = round(($lastSaldo + $journal->amount_debet - $journal->amount_kredit), 2);
                } else { //passiva
                    $journal->amount_saldo = round(($lastSaldo - $journal->amount_debet + $journal->amount_kredit), 2);
                }
                $journal->save();
                $lastSaldo = $journal->amount_saldo;
                $newdata[] = collect($journal)->only(['id', 'description', 'index_date', 'amount_saldo', 'amount_debet', 'amount_kredit']);
            }
        } catch (LockTimeoutException $e) {
            return [
                'status' => 0,
                'msg' => 'jurnal tidak berhasil masuk, antrian timeout',
                'lock' => $lock
            ];
        } finally {
            if ($isLock == true)
                $lock->release();
            // CustomLogger::log('journal', 'info', 'recalculate release lock ' . $name);
        }
        return ['status' => 1, 'msg' => $newdata, 'journal' => $thejournal];
    }

    public function scopeSearchNote($q, $search)
    {
        $searchs = explode(' ', $search);
        foreach ($searchs as $se) {
            $q->where('journals.description', 'like', '%' . $se . '%');
        }
        return $q;
    }

    public function scopeSearchCOA($q, $search)
    {
        if ($search) {

            $primaryCode = self::getPrimaryCode($search);
            $q->where('journals.code_group', 'like', '%' . $primaryCode . '%');
        }
    }

    public function scopeSearchNumber($q, $search)
    {
        if ($search) {

            $q->where('journals.journal_number', 'like', '%' . $search . '%');
        }
    }
    public function scopeSearchNameCOA($q, $search)
    {
        $searchs = explode(' ', $search);
        $q->join('chart_accounts', 'chart_accounts.code_group', '=', 'journals.code_group');
        foreach ($searchs as $se) {
            $q->where('chart_accounts.name', 'like', '%' . $se . '%');
        }
        $q->select('journals.*');
        return $q;
    }

    public function scopeSortindex($q)
    {
        $q->orderBy('journals.index_date', 'asc');
    }

    public static function getPrimaryCode($code)
    {
        $theFixCode = null;
        for ($i = 1; $i < 10000000; $i *= 10) {
            if ($code % $i != 0) {
                $theFixCode = $code * 10 / $i;
                break;
            }
        }
        if (!$theFixCode) {
            dd($code);
        }
        return $theFixCode;
    }
}
