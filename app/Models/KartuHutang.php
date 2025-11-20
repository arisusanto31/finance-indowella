<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use App\Traits\HasIndexDate;
use App\Traits\HasModelDetailKartuInvoice;
use App\Traits\HasModelSaldoUang;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

use function PHPUnit\Framework\throwException;

class KartuHutang extends Model
{
    //
    use HasIndexDate;
    use HasModelDetailKartuInvoice;
    use HasModelSaldoUang;

    protected $table = 'kartu_hutangs';

    public $timestamps = true;
    public function person()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function codeGroup()
    {
        return $this->belongsTo(ChartAccount::class, 'code_group', 'code_group');
    }
    public function codeGroupLawan()
    {
        return $this->belongsTo(ChartAccount::class, 'lawan_code_group', 'code_group');
    }

    protected static function booted()
    {

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_hutangs'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
        $lock = Cache::lock('create-kartu-utang' . $personType . '-' . $personID, 90);
        info('kartu utang - trying to create kartu utang');
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
                $invoiceNumber = $request->input('invoice_pack_number');
                $PONumber = $request->input('purchase_order_number');
                $POID = $request->input('purchase_order_id');
                $invoiceID = $request->input('invoice_pack_id');
                $realAmount = $amount_debet - $amount_kredit;
                $indexDate = self::getNextIndexDate($date);

                $lastKartu = KartuHutang::where('person_id', $personID)->where('person_type', $personType)
                    ->where('invoice_pack_number', $invoiceNumber)->where('index_date', '<', $indexDate)->orderBy('index_date', 'desc')->first();
                $lastSaldoPurchase =  $lastKartu ? $lastKartu->amount_saldo_factur : 0;
                $lastSaldoFactur = $lastSaldoPurchase;
                $lastSaldoPerson = KartuHutang::whereIn('index_date', function ($q) use ($personID, $personType, $indexDate) {
                    $q->from('kartu_hutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->where('index_date', '<', $indexDate)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('invoice_pack_number');
                })->sum('amount_saldo_factur');
                $kartu = new KartuHutang();
                $kartu->type = $request->input('type');
                $kartu->invoice_pack_number = $invoiceNumber;
                $kartu->invoice_pack_id = $invoiceID;
                $kartu->purchase_order_number = $PONumber;
                $kartu->purchase_order_id = $POID;
                $kartu->description = $request->input('description');
                $kartu->amount_saldo_purchase = $lastSaldoPurchase + $realAmount;
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
                $kartu->code_group_name = $request->input('code_group_name');
                $kartu->lawan_code_group = $request->input('lawan_code_group');
                $kartu->invoice_date = $date;
                $kartu->book_journal_id = bookID();
                $kartu->index_date = $indexDate;
                $kartu->index_date_group = createCarbon($date)->format('ymdHis');
                $kartu->save();
                if (self::isBackdate($date)) {
                    $kartu->recalculateSaldo();
                }
                $kartu->createDetailKartuInvoice();


                //wes sudah terhitung sema tinggal pudate

            } catch (LockTimeoutException $e) {
            } finally {
                $lock->release();
            }
            return [
                'status' => 1,
                'msg' => $kartu
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
            'msg' => $kartu
        ];
    }

    public static function createMutation(Request $request, $useTransaction = true)
    {
        if ($useTransaction)
            DB::beginTransaction();
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $invoiceNumber = $request->input('invoice_pack_number');
            $PONumber = $request->input('purchase_order_number');
            $invoice = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invoiceID = $invoice ? $invoice->id : null;
            $PO = PurchaseOrder::where('purchase_order_number', $PONumber)->first();
            $POID = $PO ? $PO->id : null;
            $tokoid= $request->input('toko_id');


            $amountMutasi = $request->input('amount_mutasi');
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
            $codeGroup = $request->input('code_group');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $chartAccount = ChartAccount::where('code_group', $codeGroup)->first();
            if (!$chartAccount) {
                throw new \Exception('chart account not found');
            }
            $desc = $request->input('description');
            $codeGroupName = $chartAccount->name;
            $person = $personType::find($personID);
            if (!$person) {
                throw new \Exception('person not found');
            }
            if ($isOtomatisJurnal) {
                $kredits = [
                    [
                        'code_group' => $codeGroup,
                        'description' => $desc,
                        'amount' => $amountMutasi,
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko_id'=>$tokoid
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $lawanCodeGroup,
                        'description' => $desc,
                        'amount' => $amountMutasi,
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko_id'=>$tokoid
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'purchasing',
                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation purchase',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) {
                    if ($useTransaction)
                        DB::rollBack();
                    return $st;
                }
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->where('code_group', $codeGroup)->first();
                $journalID = $journal->id;
            } else {
                $journalID = null;
                $number = null;
            }
            $st = self::createKartu(new Request([
                'type' => 'mutasi',
                'purchasing_id' => null,
                'invoice_pack_id' => $invoiceID,
                'invoice_pack_number' => $invoiceNumber,
                'purchase_order_id' => $POID,
                'purchase_order_number' => $PONumber,
                'description' => $desc,
                'amount_debet' => $amountMutasi, //debet untuk nilai utang yang bertambah
                'amount_kredit' => 0,
                'reference_id' => null,
                'reference_type' => null,
                'person_id' => $personID,
                'person_type' => $personType,
                'journal_number' => $number,
                'journal_id' => $journalID,
                'code_group' => $codeGroup,
                'lawan_code_group' => $lawanCodeGroup,
                'code_group_name' => $codeGroupName,
                'date' => $date
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

    public static function createPelunasan(Request $request, $useTransaction = true)
    {
        if ($useTransaction)
            DB::beginTransaction();
        try {
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $invoiceNumber = $request->input('invoice_pack_number');
            $PONumber = $request->input('purchase_order_number');
            $invoice = InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invoiceID =  $invoice ? $invoice->id : null;
            $PO = PurchaseOrder::where('purchase_order_number', $PONumber)->first();
            $POID = $PO ? $PO->id : null;
            $amountBayar = $request->input('amount_bayar');
            $codeGroup = $request->input('code_group');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeName = ChartAccount::where('code_group', $codeGroup)->first()?->name;
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $description = $request->input('description');
            if ($amountBayar > 0) {
                $codeKredit = $lawanCodeGroup;
                $codeDebet = $codeGroup;
                $desc = $description;
            } else {
                $codeKredit = $codeGroup;
                $codeDebet = $lawanCodeGroup;
                $desc = $description;
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
                    'type' => 'purchasing',
                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation purchase',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) {
                    if ($useTransaction)
                        DB::rollBack();
                    return $st;
                }
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->where('code_group', $codeGroup)->first();
                $journalID = $journal->id;
            } else {
                $number = null;
                $journalID = null;
            }
            $amountKredit = $amountBayar > 0 ? $amountBayar : 0;
            $amountDebet = $amountBayar < 0 ? abs($amountBayar) : 0;
            $st = self::createKartu(new Request([
                'type' => 'pelunasan',
                'purchasing_id' => null,
                'invoice_pack_number' => $invoiceNumber,
                'invoice_pack_id' => $invoiceID,
                'purchase_order_id' => $POID,
                'purchase_order_number' => $PONumber,
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
                'date'=>$date
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
        $kartus = KartuHutang::where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where('invoice_pack_number', $this->invoice_pack_number)->where('index_date', '>', $this->index_date)->get();

        $saldo = $this->amount_saldo_factur;
        foreach ($kartus as $kartu) {
            $saldo = $saldo + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_factur = $saldo;
            $kartu->save();
        }

        $kartuOrang = KartuHutang::where('person_id', $this->person_id)->where('person_type', $this->person_type)
            ->where('index_date', '>', $this->index_date)->get();
        $saldoOrang = $this->amount_saldo_person;
        foreach ($kartuOrang as $kartu) {
            $saldoOrang = $saldoOrang + $kartu->amount_debet - $kartu->amount_kredit;
            $kartu->amount_saldo_person = $saldoOrang;
            $kartu->save();
        }
    }
}
