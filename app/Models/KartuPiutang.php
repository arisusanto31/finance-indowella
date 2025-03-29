<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class KartuPiutang extends Model
{
    //

    protected $table='kartu_piutangs';
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
        $lock = Cache::lock('create-kartu-piutang' . $personType . '-' . $personID, 90);
        info('kartu utang - trying to create kartu piutang');
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


                $lastKartu = KartuPiutang::where('person_id', $personID)->where('person_type', $personType)
                    ->where('package_number', $facturNumber)->orderBy('id', 'desc')->first();
                $lastSaldo =  $lastKartu ? $lastKartu->amount_saldo_factur : 0;
                $lastSaldoFactur = $lastSaldo;


                $lastSaldoPerson = KartuHutang::whereIn('id', function ($q) use ($personID, $personType) {
                    $q->from('kartu_piutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('package_number');
                })->sum('amount_saldo_factur');
                $kartu = new KartuHutang();
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
                $kartu->journal_id= $request->input('journal_id');
                $kartu->code_group = $request->input('code_group');
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


    public static function createMutation(Request $request)
    {
        
        DB::beginTransaction();
        try {
            $factur = $request->input('package_number');
            $amountMutasi = $request->input('amount_mutasi');
            $personID = $request->input('person_id');
            $personType = $request->input('person_type');
            $accountPenjualan = $request->input('accountPenjualan');
            $accountPiutang= $request->input('account_piutang');

            if($amountMutasi>0){
                //piutang bertambah
                $codeDebet= $accountPiutang;
                $codeKredit= $accountPenjualan;
                $amountDebet= $amountMutasi; $amountKredit=0;
                $desc= 'claim piutang dari penjualan '.$factur;
            }
            else{
                $codeDebet= $accountPenjualan;
                $codeKredit= $accountPiutang;
                $amountKredit=abs($amountMutasi); $amountDebet=0;
                $desc= 'pembatalan penjualan '.$factur;
            }
            $kredits = [
                [
                    'code_group' => $codeKredit,
                    'description' => $desc,
                    'amount' => abs($amountMutasi),
                    'reference_id' => null,
                    'reference_type' => null,
                ],
            ];
            $debets = [
                [
                    'code_group' => $codeDebet,
                    'description' => $desc,
                    'amount' => abs($amountMutasi),
                    'reference_id' => null,
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
                'url_try_again' => null

            ]), false);
            if ($st['status'] == 0) return $st;
            $number = $st['journal_number'];
            $journal = Journal::where('journal_number', $number)->whereBetween('code_group',[120000,130000])->first();

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
                'journal_id' => $journal->id,
                'code_group' => $journal->code_group,
                'lawan_code_group' => $journal->lawan_code_group
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
            $accountBayar = $request->input('account_bayar');
            $accountPiutang= $request->input('account_piutang');

            if($amountBayar>0){
                $codeDebet= $accountBayar;
                $codeKredit=$accountPiutang;
                $desc='penerimaan penjualan '.$factur;
            }
            else{
                $codeDebet=$accountPiutang;
                $codeKredit= $accountBayar;
                $desc='pembatalan penerimaan penjualan '.$factur;
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
                'type' => 'transaction',
                'date' => Date('Y-m-d H:i:s'),
                'is_auto_generated' => 1,
                'title' => 'create penerimaan penjualan',
                'url_try_again' => null

            ]), false);
            if ($st['status'] == 0) return $st;
            $number = $st['journal_number'];
            $journal = Journal::where('journal_number', $number)->whereBetween('code_group',[120000,130000] )->first();
            $amountKredit= $amountBayar>0?$amountBayar:0;
            $amountDebet= $amountBayar<0?abs($amountBayar):0;
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
                'journal_id' => $journal->id,
                'code_group' => $journal->code_group,
                'lawan_code_group' => $journal->lawan_code_group
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
