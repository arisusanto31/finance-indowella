<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

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
                $purchasing_id = $request->input('purchasing_id');
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
                    ->where('purchasing_id', $purchasing_id)->orderBy('id', 'desc')->first();
                $lastSaldoPurchase =  $lastKartu ? $lastKartu->amount_saldo_purchase : 0;

                $lastSaldoFactur = KartuHutang::whereIn('id', function ($q) use ($facturNumber) {
                    $q->from('kartu_hutangs')->where('factur_supplier_number', $facturNumber)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('purchasing_id');
                })->sum('amount_saldo_purchase');


                $lastSaldoPerson = KartuHutang::whereIn('id', function ($q) use ($personID, $personType) {
                    $q->from('kartu_hutangs')->where('person_id', $personID)->where('person_type', $personType)
                        ->select(
                            DB::raw('max(id) as maxid'),
                        )->groupBy('purchasing_id');
                })->sum('amount_saldo_purchase');
                $kartu = new KartuHutang();
                $kartu->type = $request->input('type');
                $kartu->purchasing_id = $request->input('purchasing_id');
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
                $kartu->code_group = $request->input('code_group');
                $kartu->lawan_code_group = $request->input('lawan_code_group');
                $kartu->save();


                //wes sudah terhitung sema tinggal pudate

            } catch (LockTimeoutException $e) {
            } finally {
                $lock->release();
            }
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
        $factur = $request->input('factur_supplier_number');
        $amountMutasi= $request->input('amount_mutasi');
        $personID= $request->input('person_id');
        $personType= $request->input('person_type');
        
        $kredits = [
            [
                'code_group' => 211000,
                'description' => 'hutang pembelian '.$factur,
                'amount' => $amountMutasi,
                'reference_id' => null,
                'reference_type' => null,
            ],
        ];
        $debets = [
            [
                'code_group' => 140001,
                'description' => 'penambahan persediaan barang dari ' . $factur,
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
            'url_try_again' =>null

        ]));
        if($st['status']==0)return $st;
        $number = $st['journal_number'];
        $journal= Journal::where('journal_number',$number)->where('code_group',211000)->first();

        return self::createKartu(new Request([
            'type' => 'purchase-mutation',
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
            'journal_id'=>$journal->id,
            'code_group' => $journal->code_group,
            'lawan_code_group' => $journal->lawan_code_group
        ]));
    }

 

    public static function createPembatalanMutationPurchase(Request $request)
    {
        $id = $request->input('purchasing_id');
        $mdsid = $request->input('mutation_detail_id');
        $purchase = Purchasing::find($id);
        $md = MutationDetail::find($mdsid);
        $journal = Journal::where('journal_number', $request->input('journal_number'))->first();

        return self::createKartu(new Request([
            'type' => 'purchase-pembatalan_mutation',
            'purchasing_id' => $purchase->id,
            'factur_supplier_number' => $purchase->factur_supplier_number,
            'description' => 'pembatalan dari mutasi purchase ' . $purchase->id . ' (' . $purchase->factur_supplier_number . ')',
            'amount_kredit' => abs($journal->amount_debet + $journal->amount_kredit), //kredit untuk nilai utang yang berkurang
            'amount_debet' => 0,
            'reference_id' => $md->id, //mutasi lama yang dibatalkan
            'reference_type' => get_class($md),
            'person_id' => $purchase->supplier_id,
            'person_type' => get_class($purchase->supplier),
            'journal_number' => $journal->journal_number,
            'code_group' => $journal->code_group,
            'lawan_code_group' => $journal->lawan_code_group

        ]));
    }

    public static function createPembayaranPurchase(Request $request)
    {
        $detailBayar = DetailPembayaranHutang::find($request->input('detail_bayar_id'));
        $journal = Journal::where('journal_number', $request->input('journal_number'))->first();
        $purchase = $detailBayar->purchasing;
        if ($detailBayar->real_amount > 0) {
            return self::createKartu(new Request([
                'type' => 'purchase-pembayaran',
                'purchasing_id' => $purchase->id,
                'factur_supplier_number' => $purchase->factur_supplier_number,
                'description' => 'pembayaran purchase ' . $purchase->id . ' (' . $purchase->factur_supplier_number . ')',
                'amount_kredit' => abs($detailBayar->amount), //kredit untuk nilai utang yang berkurang
                'amount_debet' => 0,
                'reference_id' => $detailBayar->id, //mutasi lama yang dibatalkan
                'reference_type' => get_class($detailBayar),
                'person_id' => $purchase->supplier_id,
                'person_type' => get_class($purchase->supplier),
                'journal_number' => $journal->journal_number,
                'code_group' => $journal->code_group,
                'lawan_code_group' => $journal->lawan_code_group
            ]));
        } else {
            return self::createKartu(new Request([
                'type' => 'purchase-refund',
                'purchasing_id' => $purchase->id,
                'factur_supplier_number' => $purchase->factur_supplier_number,
                'description' => 'refund purchase ' . $purchase->id . ' (' . $purchase->factur_supplier_number . ')',
                'amount_debet' => abs($detailBayar->amount), //debet untuk nilai utang yang bertambah
                'amount_kredit' => 0,
                'reference_id' => $detailBayar->id, //mutasi lama yang dibatalkan
                'reference_type' => get_class($detailBayar),
                'person_id' => $purchase->supplier_id,
                'person_type' => get_class($purchase->supplier),
                'journal_number' => $journal->journal_number,
                'code_group' => $journal->code_group,
                'lawan_code_group' => $journal->lawan_code_group
            ]));
        }
    }
}
