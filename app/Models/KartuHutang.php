<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

use function PHPUnit\Framework\throwException;

class KartuHutang extends Model
{
    //
    protected $table = 'kartu_hutangs';

    public $timestamps = true;
    public function person()
    {
        return $this->morphTo();
    }

    public function reference()
    {
        return $this->mrophTo();
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
                $facturNumber = $request->input('factur_supplier_number');
                $realAmount = $amount_debet - $amount_kredit;


                $lastKartu = KartuHutang::where('person_id', $personID)->where('person_type', $personType)
                    ->where('factur_supplier_number', $facturNumber)->orderBy('id', 'desc')->first();
                $lastSaldoPurchase =  $lastKartu ? $lastKartu->amount_saldo_factur : 0;
                $lastSaldoFactur = $lastSaldoPurchase;


                $lastSaldoPerson = KartuHutang::whereIn('id', function ($q) use ($personID, $personType) {
                    $q->from('kartu_hutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('factur_supplier_number');
                })->sum('amount_saldo_factur');
                $kartu = new KartuHutang();
                $kartu->type = $request->input('type');
                $kartu->factur_supplier_number = $facturNumber;
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
                $kartu->invoice_date = Date('Y-m-d');
                $kartu->save();



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
            $factur = $request->input('factur_supplier_number');
            $amountMutasi = $request->input('amount_mutasi');
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
            $codeGroup = $request->input('code_group');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $person = $personType::find($personID);
            if (!$person) {
                throw new \Exception('person not found');
            }
            $kredits = [
                [
                    'code_group' => $codeGroup,
                    'description' => 'hutang nomer' . $factur . ' dari ' . $person->name,
                    'amount' => $amountMutasi,
                    'reference_id' => null,
                    'reference_type' => null,
                ],
            ];
            $debets = [
                [
                    'code_group' => $lawanCodeGroup,
                    'description' => 'hutang nomer' . $factur . ' dari ' . $person->name,
                    'amount' => $amountMutasi,
                    'reference_id' => null,
                    'reference_type' => null,
                ],
            ];
            $st = JournalController::createBaseJournal(new Request([
                'kredits' => $kredits,
                'debets' => $debets,
                'type' => 'purchasing',
                'date' => Date('Y-m-d H:i:s'),
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
            $journal = Journal::where('journal_number', $number)->where('code_group', 211000)->first();
            $chart = $journal->chartAccount;
            $st = self::createKartu(new Request([
                'type' => 'mutasi',
                'purchasing_id' => null,
                'factur_supplier_number' => $factur,
                'description' => 'claim utang dari mutasi pembelian ' . $factur,
                'amount_debet' => $amountMutasi, //debet untuk nilai utang yang bertambah
                'amount_kredit' => 0,
                'reference_id' => null,
                'reference_type' => null,
                'person_id' => $personID,
                'person_type' => $personType,
                'journal_number' => $number,
                'journal_id' => $journal->id,
                'code_group' => $codeGroup,
                'lawan_code_group' => $lawanCodeGroup,
                'code_group_name' => $chart->name
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
            $factur = $request->input('factur_supplier_number');
            $amountBayar = $request->input('amount_bayar');
            $codeGroup = $request->input('code_group');
            $lawanCodeGroup = $request->input('lawan_code_group');
            $codeName = ChartAccount::where('code_group', $codeGroup)->first()?->name;

            if ($amountBayar > 0) {
                $codeKredit = $lawanCodeGroup;
                $codeDebet = $codeGroup;
                $desc = 'pembayaran hutang ' . $factur;
            } else {
                $codeKredit = $codeGroup;
                $codeDebet = $lawanCodeGroup;
                $desc = 'pembatalan pembayaran hutang ' . $factur;
            }
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
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
                'date' => Date('Y-m-d H:i:s'),
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
            $journal = Journal::where('journal_number', $number)->where('code_group', 211000)->first();
            $amountKredit = $amountBayar > 0 ? $amountBayar : 0;
            $amountDebet = $amountBayar < 0 ? abs($amountBayar) : 0;
            $st = self::createKartu(new Request([
                'type' => 'pelunasan',
                'purchasing_id' => null,
                'factur_supplier_number' => $factur,
                'description' => $desc,
                'amount_debet' => $amountDebet, //debet untuk nilai utang yang bertambah
                'amount_kredit' => $amountKredit,
                'reference_id' => null,
                'reference_type' => null,
                'person_id' => $personID,
                'person_type' => $personType,
                'journal_number' => $number,
                'journal_id' => $journal->id,
                'code_group' => $codeGroup,
                'lawan_code_group' => $lawanCodeGroup,
                'code_group_name' => $codeName
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
}
