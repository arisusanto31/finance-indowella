<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Str;

class KartuPrepaidExpense extends Model
{
    //
    protected $table = "kartu_prepaid_expenses";
    public $timestamps = true;
    public $fillable = [
        'book_journal_id',
        'prepaid_expense_id',
        'amount',
        'date',
        'type_mutasi',
        'nilai_buku',
        'code_group',
        'lawan_code_group',
    ];


    protected static function booted()
    {

        static::addGlobalScope('book_journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_prepaid_expenses'; // untuk dukung alias `j` kalau pakai from('journals as j')
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
        $invID = $request->input('prepaid_expense_id');
        $lock = Cache::lock('prepaid-' . $invID, 30);
        //diini kita ambil dulu kartu yang lama
        try {
            $lock->block(15);
            $lastKartu = KartuPrepaidExpense::where('prepaid_expense_id', $invID)->orderBy('id', 'desc')->first();
            $lastNilaiBuku = $lastKartu ? $lastKartu->nilai_buku : 0;
            // Format nilai amount terlebih dahulu
            //input amount harus pakai languange indonesia
            $formattedAmount = $request->input('type_mutasi') == 'amortisasi' ? format_db($request->input('amount')) * -1 : format_db($request->input('amount'));
            $request->merge([
                'book_journal_id' => session('book_journal_id'),
                'amount'          => $formattedAmount,
                // nilai_buku dihitung berdasarkan kartu terakhir dan amount yang sudah diformat
                'nilai_buku'      => bcadd($lastNilaiBuku, $formattedAmount),
            ]);
            $validated = $request->validate([
                'prepaid_expense_id'    => 'required|integer',
                'book_journal_id' => 'required|integer',
                'amount'          => 'required|numeric',
                'type_mutasi'     => 'required|string', // validasi sesuai kebutuhan
                'nilai_buku'      => 'required|numeric',
                'date'           => 'required|date',
                'code_group'     => 'required|numeric',
                'lawan_code_group' => 'required|numeric',
            ]);
            $ki = KartuPrepaidExpense::create($validated);
            //masukkan data baru dengan nilai buku yang baru yaa..
            return ['status' => 1, 'msg' => $ki];
        } catch (ValidationException $e) {
            $lock->release();
            return [
                'status' => 0,
                'msg' => getErrorValidation($e)
            ];
        } catch (LockTimeoutException $e) {
            $lock->release();
            return ['status' => 0, 'msg' => 'lock time error'];
        } catch (Throwable $th) {
            $lock->release();
            return [
                'status' => 0,
                'msg' => $th->getMessage()
            ];
        } finally {
            $lock->release();
        }
        return [
            'status' => 0,
            'msg' => 'something error ?'
        ];
    }
}
