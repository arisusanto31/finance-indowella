<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class KartuDPSales extends Model
{
    //
    protected $table = 'kartu_dp_sales';
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
            $from = $query->getQuery()->from ?? 'kartu_dp_sales'; // untuk dukung alias `j` kalau pakai from('journals as j')
            if (Str::contains($from, ' as ')) {
                [$table, $alias] = explode(' as ', $from);
                $alias = trim($alias);
            } else {
                $alias = $from;
            }

            $query->where(function ($q) use ($alias) {
                $q->whereNull("{$alias}.book_journal_id")
                    ->orWhere("{$alias}.book_journal_id", session('book_journal_id'));
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
                $facturNumber = $request->input('package_number');
                $realAmount = $amount_debet - $amount_kredit;


                $lastKartu = KartuDPSales::where('person_id', $personID)->where('person_type', $personType)
                    ->where('package_number', $facturNumber)->orderBy('id', 'desc')->first();
                $lastSaldo =  $lastKartu ? $lastKartu->amount_saldo_factur : 0;
                $lastSaldoFactur = $lastSaldo;


                $lastSaldoPerson = KartuDPSales::whereIn('id', function ($q) use ($personID, $personType) {
                    $q->from('kartu_piutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('package_number');
                })->sum('amount_saldo_factur');
                $kartu = new KartuDPSales();
                $kartu->type = $request->input('type');
                $kartu->package_number = $facturNumber;
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
                $kartu->invoice_date = Date('Y-m-d');
                $kartu->book_journal_id = session('book_journal_id');
                $kartu->save();



                //wes sudah terhitung sema tinggal pudate

            } catch (LockTimeoutException $e) {
            } finally {
                $lock->release();
            }
            info('kartu piutang - success create kartu piutang');
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


    public static function createMutation(Request $request)
    {
        DB::beginTransaction();
        try {
            $factur = $request->input('package_number');
            $amountMutasi = $request->input('amount_mutasi');
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeGroup = $request->input('code_group');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $desc = $request->input('description');
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            if (!$chart) {
                throw new \Exception('chart not found');
            }
            $codeName = $chart->name;
            $invoicePack = SalesOrder::where('sales_order_number', $factur)->first();
            if (!$invoicePack) {
                $tokoid = null;
            } else {
                $tokoid = $invoicePack->toko_id;
            }
            if ($amountMutasi < 0) {
                //piutang bertambah
                $codeDebet = $codeGroup;
                $codeKredit = $lawanCodeGroup;
                $amountDebet = 0;
                $amountKredit = abs($amountMutasi);
            } else {
                $codeDebet = $lawanCodeGroup;
                $codeKredit = $codeGroup;
                $amountKredit = 0;
                $amountDebet = $amountMutasi;
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
                    'date' => Date('Y-m-d H:i:s'),
                    'is_auto_generated' => 1,
                    'title' => 'create mutation transaction',
                    'url_try_again' => 'try_again'

                ]), false);
                if ($st['status'] == 0) return $st;

                $number = $st['journal_number'];
                info('success harusnya dari sini journalnya aman' . $number);
                $journal = Journal::where('journal_number', $number)->where('code_group', 214000)->first();
                if (!$journal) {
                    return ['status' => 0, 'msg' => 'kok aneh journal not found 214000'];
                }
                $journalID = $journal->id;
            } else {
                $number = null;
                $journalID = null;
            }
            $st = self::createKartu(new Request([
                'type' => 'mutasi',
                'package_number' => $factur,
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
                'code_group_name' => $codeName
            ]));

            if ($st['status'] == 1) {
                DB::commit();
                return $st;
            } else {
                DB::rollBack();
                return $st;
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }





    public static function createPelunasan(Request $request)
    {


        DB::beginTransaction();
        try {
            $factur = $request->input('package_number');
            $amountBayar = $request->input('amount_bayar');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeGroup = $request->input('code_group');
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal') ?? 0;
            $chart = ChartAccount::where('code_group', $codeGroup)->first();
            $invoicePack = SalesOrder::where('sales_order_number', $factur)->first();
            if (!$invoicePack) {
                $invoicePack= InvoicePack::where('invoice_number', $factur)->first();

                $tokoid = null;

            } else {
                $tokoid = $invoicePack->toko_id;
            }
            $desc = $request->input('description');
            if (!$chart) {
                throw new \Exception('chart not found');
            }
            $codeName = $chart->name;
            if ($amountBayar < 0) {
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
                        'toko_id'=>$tokoid,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => $desc,
                        'amount' => abs($amountBayar),
                        'reference_id' => null,
                        'reference_type' => null,
                        'toko_id'=>$tokoid,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'transaction',
                    'date' => Date('Y-m-d H:i:s'),
                    'is_auto_generated' => 1,
                    'title' => 'create penerimaan penjualan',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) return $st;
                $number = $st['journal_number'];
                info('success harusnya dari sini journalnya aman' . $number);
                $journal = Journal::where('journal_number', $number)->where('code_group', 214000)->first();
                if (!$journal) {
                    return ['status' => 0, 'msg' => 'kok aneh journal not found 214000'];
                }
                $journalID = $journal->id;
            } else {
                $journalID = null;
                $number = null;
            }
            $amountKredit = $amountBayar > 0 ? $amountBayar : 0;
            $amountDebet = $amountBayar < 0 ? abs($amountBayar) : 0;
            $st = self::createKartu(new Request([
                'type' => 'pelunasan',
                'package_number' => $factur,
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
                'code_group_name' => $codeName
            ]));

            if ($st['status'] == 1) {
                DB::commit();
                return $st;
            } else {
                DB::rollBack();
                return $st;
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        }
    }
}
