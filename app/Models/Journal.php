<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Journal extends Model
{
    use HasFactory;
    protected $table = "journals";

    public function reference()
    {
        return $this->morphTo();
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

    // 'reference_id' => $transaction->id,
    // 'reference_type' => get_class($transaction),
    public static function generateJournal(Request $request)
    {
        $debet = $request->input('amount_debet');
        $kredit = $request->input('amount_kredit');
        $codeGroup = $request->input('code_group');
        $chartAccount = ChartAccount::where('code_group', $codeGroup)->first();
        $coaID = $chartAccount->id;
        $journal_number = $request->input('journal_number');

        $lock = Cache::lock('generate-journal' . $coaID, 11);
        try {

            $lock->block(5);
            try {
                $lastJournal = Journal::where('chart_account_id', $coaID)->orderBy('index_date', 'desc')->first();
                $journal = new Journal;
                $journal->chart_account_id = $coaID;
                $journal->journal_number = $journal_number;
                $journal->code_group = $request->input('code_group');
                $journal->description = $request->input('description');
                $journal->amount_debet = $debet > 0 ? $debet : 0;
                ;
                $journal->amount_kredit = $kredit > 0 ? $kredit : 0;
                if ($chartAccount->account_type == 'Aset') {
                    $theAmount = $journal->amount_debet - $journal->amount_kredit;
                    info($codeGroup . '-aktiva');
                    info($theAmount);
                } else {
                    $theAmount = $journal->amount_kredit - $journal->amount_debet;
                    info($codeGroup . '-passiva');
                    info($theAmount);
                }
                $journal->reference_id = $request->input('reference_id');
                $journal->reference_type = $request->input('reference_type');
                $lastSaldo = $lastJournal ? $lastJournal->amount_saldo : 0;
                $journal->amount_saldo = $lastSaldo + $theAmount;
                info($lastSaldo . '+' . $theAmount);
                $now = carbonDate();
                $indexDate = $now->format('ymdHis') . (substr($now->format('u'), 0, 2));
                $journal->index_date = $indexDate;
                $journal->is_auto_generated = $request->input('is_auto_generated');
                $journal->save();
                $reference = $journal->reference;
                if ($reference) {
                    if (get_class($reference) != 'App\Models\\Journal') {
                        $reference->journal_number = $journal->journal_number;
                        $reference->save();
                    }
                }
            } catch (\Exception $e) {
                $lock->release();
                return [
                    'status' => 0,
                    'msg' => $e->getMessage()
                ];
            }

        } catch (LockTimeoutException $e) {
        } finally {
            $lock->release();
        }
        return [
            'status' => 1,
            'msg' => $journal
        ];
    }

    public function scopeSearchNote($q,$search){
        $searchs=explode(' ',$search);
        foreach($searchs as $se){
            $q->where('journals.description','like','%'.$se.'%');
        }
        return $q;
    }

    public static function createSaldoMutation($number)
    {
        // $journals= Journall

    }



}
