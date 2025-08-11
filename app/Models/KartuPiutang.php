<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use App\Services\LockManager;
use App\Traits\HasIndexDate;
use App\Traits\HasModelDetailKartuInvoice;
use App\Traits\HasModelSaldoUang;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Str;

class KartuPiutang extends Model
{
    //
    use HasIndexDate;
    use HasModelDetailKartuInvoice;
    use HasModelSaldoUang;
    protected $table = 'kartu_piutangs';
    public $timestamps = true;
    public function person()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->mrophTo();
    }


    protected static function booted()
    {

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_piutangs'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
    public static function createKartu(Request $request)
    {

        $personID = $request->input('person_id');
        $personType = $request->input('person_type');
        $lock = Cache::lock('create-kartu-piutang' . $personType . '-' . $personID, 90);
        info('kartu piutang - trying to create kartu piutang');
        try {

            try {
                $lock->block(30);
                $date = $request->input('date') ?? now();
                self::proteksiBackdate($date);
                $amount_debet = $request->input('amount_debet');
                $amount_kredit = $request->input('amount_kredit');
                if ($amount_debet < 0) {
                    //dibalik langsung aja kalo parameternya ada yang salah
                    $amount_kredit = abs($amount_debet);
                    $amount_debet = 0;
                } else if ($amount_kredit < 0) {
                    //dibalik langsung aja kalo parameternya ada yang salah
                    $amount_debet = abs($amount_kredit);
                    $amount_kredit = 0;
                }
                $SONumber = $request->input('sales_order_number');
                $invoiceNumber = $request->input('invoice_pack_number');
                $SOID = $request->input('sales_order_id');
                $invoiceID = $request->input('invoice_pack_id');
                $realAmount = $amount_debet - $amount_kredit;
                $indexDate = self::getNextIndexDate($date);

                $lastKartu = KartuPiutang::where('person_id', $personID)->where('person_type', $personType)
                    ->where('invoice_pack_number', $invoiceNumber)->where('index_date', '<', $indexDate)->orderBy('index_date', 'desc')->first();
                $lastSaldo =  $lastKartu ? $lastKartu->amount_saldo_factur : 0;
                $lastSaldoFactur = $lastSaldo;


                $lastSaldoPerson = KartuPiutang::whereIn('index_date', function ($q) use ($personID, $personType, $indexDate) {
                    $q->from('kartu_piutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->where('index_date', '<', $indexDate)
                        ->select(
                            DB::raw('max(index_date) as maxid'),
                        )->groupBy('invoice_pack_number');
                })->sum('amount_saldo_factur');
                $kartu = new KartuPiutang();
                $kartu->type = $request->input('type');
                $kartu->sales_order_number = $SONumber;
                $kartu->invoice_pack_number = $invoiceNumber;
                $kartu->sales_order_id = $SOID;
                $kartu->invoice_pack_id = $invoiceID;
                $kartu->description = $request->input('description');
                $kartu->amount_saldo_transaction = $lastSaldo + $realAmount;
                $kartu->amount_saldo_factur = $lastSaldoFactur + $realAmount;
                $kartu->amount_saldo_person = $lastSaldoPerson + $realAmount;
                $kartu->amount_debet = $amount_debet;
                $kartu->amount_kredit = $amount_kredit;
                $kartu->reference_id = $request->input('reference_id');
                $kartu->reference_type = $request->input('reference_type');
                $kartu->person_id = $request->input('person_id');
                $kartu->person_type = $request->input('person_type');
                $kartu->journal_number = $request->input('journal_number');
                $kartu->journal_id = $request->input('journal_id');
                $kartu->code_group = $request->input('code_group');
                $kartu->lawan_code_group = $request->input('lawan_code_group');
                $kartu->code_group_name = $request->input('code_group_name');
                $kartu->invoice_date = $date;
                $kartu->book_journal_id = bookID();
                $kartu->index_date = $indexDate;
                $kartu->index_date_group = createCarbon($date)->format('ymdHis');
                $kartu->save();
                if (self::isBackdate($date)) {
                    $kartu->recalculateSaldo();
                }



                $kartu->createDetailKartuInvoice();
            } catch (LockTimeoutException $e) {
            } finally {
                $lock->release();
            }
            info('kartu piutang - success create kartu piutang');
            return [
                'status' => 1,
                'msg' => $kartu,
                'journal_number' => $kartu->journal_number,
            ];
        } catch (Throwable $th) {
            $lock->release();
            info('kartu - update kartu bermasalah' . $th->getMessage());
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
        return [
            'status' => 1,
            'msg' => $kartu,
            'journal_number' => $kartu->journal_number,
        ];
    }

    public static function createMutation(Request $request, $useTransaction = true, ?LockManager $lockManager = null)
    {
        if ($useTransaction)
            DB::beginTransaction();
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $SONumber = $request->input('sales_order_number');
            $amountMutasi = $request->input('amount_mutasi');
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeGroup = $request->input('code_group');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            if (!$chart) {
                throw new \Exception('chart not found');
            }
            $desc = $request->input('description');
            $codeName = $chart->name;
            $sales = SalesOrder::where('sales_order_number', $SONumber)->first();
            $SOID = $sales ? $sales->id : null;
            $invoiceNumber = $request->input('invoice_pack_number');
            $invoice = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invoiceID = $invoice ? $invoice->id : null;
            if (!$invoice) {
                $tokoid = null;
            } else {
                $tokoid = $invoice->toko_id;
            }
            if ($amountMutasi > 0) {
                //piutang bertambah
                $codeDebet = $codeGroup;
                $codeKredit = $lawanCodeGroup;
                $amountDebet = $amountMutasi;
                $amountKredit = 0;
            } else {
                $codeDebet = $lawanCodeGroup;
                $codeKredit = $codeGroup;
                $amountKredit = abs($amountMutasi);
                $amountDebet = 0;
            }
            if ($isOtomatisJurnal) {
                $kredits = [
                    [
                        'code_group' => $codeKredit,
                        'description' => $desc,
                        'amount' => abs($amountMutasi),
                        'reference_id' => null,
                        'toko_id' => $tokoid,
                        'reference_type' => null,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => $desc,
                        'amount' => abs($amountMutasi),
                        'reference_id' => null,
                        'toko_id' => $tokoid,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'transaction',
                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation transaction',
                    'url_try_again' => 'try_again'

                ]), false, $lockManager);
                if ($st['status'] == 0) return $st;
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->whereBetween('code_group', [120000, 130000])->first();
                $journalID = $journal->id;
            } else {
                $number = null;
                $journalID = null;
            }
            $st = self::createKartu(new Request([
                'type' => 'mutasi',
                'sales_order_number' => $SONumber,
                'invoice_pack_number' => $invoiceNumber,
                'sales_order_id' => $SOID,
                'invoice_pack_id' => $invoiceID,
                'description' => $desc,
                'amount_debet' => $amountDebet, //debet untuk nilai piutang yang bertambah
                'amount_kredit' => $amountKredit,
                'reference_id' => null,
                'reference_type' => null,
                'person_id' => $personID,
                'person_type' => $personType,
                'journal_number' => $number,
                'journal_id' => $journalID,
                'code_group' => $codeGroup,
                'lawan_code_group' => $lawanCodeGroup,
                'code_group_name' => $codeName,
                'date' => $request->input('date') ?? now()
            ]));

            if ($st['status'] == 1) {
                if ($useTransaction)
                    DB::commit();
                return $st;
            } else {
                if ($useTransaction)
                    DB::rollBack();
                return $st;
            }
        } catch (Throwable $th) {
            if ($useTransaction)
                DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }





    public static function createPelunasan(Request $request, $useTransaction = true, ?LockManager $lockManager = null)
    {

        if ($useTransaction)
            DB::beginTransaction();
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $SONumber = $request->input('sales_order_number');
            $sales = SalesOrder::where('sales_order_number', $SONumber)->first();
            $SOID = $sales ? $sales->id : null;
            $invoiceNumber = $request->input('invoice_pack_number');
            $invoice = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invoiceID = $invoice ? $invoice->id : null;
            $amountBayar = $request->input('amount_bayar');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeGroup = $request->input('code_group');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $desc = $request->input('description');

            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            if (!$chart) {
                throw new \Exception('chart not found');
            }
            $codeName = $chart->name;
            if ($amountBayar > 0) {
                $codeDebet = $lawanCodeGroup;
                $codeKredit = $codeGroup;
            } else {
                $codeDebet = $codeGroup;
                $codeKredit = $lawanCodeGroup;
            }
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');

            if ($isOtomatisJurnal) {
                $kredits = [
                    [
                        'code_group' => $codeKredit,
                        'description' => $desc,
                        'amount' => abs($amountBayar),
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => $desc,
                        'amount' => abs($amountBayar),
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'transaction',
                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'create penerimaan penjualan',
                    'url_try_again' => null

                ]), false, $lockManager);
                if ($st['status'] == 0) return $st;
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->whereBetween('code_group', [120000, 130000])->first();
                $journalID = $journal->id;
            } else {
                $journalID = null;
                $number = null;
            }
            $amountKredit = $amountBayar > 0 ? $amountBayar : 0;
            $amountDebet = $amountBayar < 0 ? abs($amountBayar) : 0;
            $st = self::createKartu(new Request([
                'type' => 'pelunasan',
                'sales_order_number' => $SONumber,
                'invoice_pack_number' => $invoiceNumber,
                'sales_order_id' => $SOID,
                'invoice_pack_id' => $invoiceID,
                'description' => $desc,
                'amount_debet' => $amountDebet, //debet untuk nilai utang yang bertambah
                'amount_kredit' => $amountKredit,
                'reference_id' => null,
                'reference_type' => null,
                'person_id' => $personID,
                'person_type' => $personType,
                'journal_number' => $number,
                'journal_id' => $journalID,
                'code_group' => $codeGroup,
                'lawan_code_group' => $lawanCodeGroup,
                'code_group_name' => $codeName,
                'date' => $request->input('date') ?? now()
            ]));

            if ($st['status'] == 1) {
                if ($useTransaction)
                    DB::commit();
                return $st;
            } else {
                if ($useTransaction)
                    DB::rollBack();
                return $st;
            }
        } catch (Throwable $th) {
            if ($useTransaction)
                DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }


    public function recalculateSaldo()
    {
        $kartus = KartuPiutang::where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where('invoice_pack_number', $this->invoice_pack_number)->where('index_date', '>', $this->index_date)->get();

        $saldo = $this->amount_saldo_factur;
        foreach ($kartus as $kartu) {
            $saldo = $saldo + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_factur = $saldo;
            $kartu->save();
        }

        $kartuOrang = KartuPiutang::where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where('index_date', '>', $this->index_date)->get();
        $saldoOrang = $this->amount_saldo_person;
        foreach ($kartuOrang as $kartu) {
            $saldoOrang = $saldoOrang + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_person = $saldoOrang;
            $kartu->save();
        }
    }
}
