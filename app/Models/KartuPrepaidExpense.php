<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
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
        'journal_id',
        'journal_number',
        'description',
        'toko_id',
        'code_group_name',
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
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal');
            $request->merge([
                'book_journal_id' => session('book_journal_id'),
                'amount'          => $formattedAmount,
                // nilai_buku dihitung berdasarkan kartu terakhir dan amount yang sudah diformat
                'nilai_buku'      => bcadd($lastNilaiBuku, $formattedAmount),
            ]);
            $chartAccount = ChartAccount::where('code_group', $request->input('code_group'))->first();
            if ($chartAccount) {
                $request->merge([
                    'code_group_name' => $chartAccount->name,
                ]);
            } else {
                throw new \Exception('Gagal mendapatkan data chart account');
            }
            $validated = $request->validate([
                'prepaid_expense_id'    => 'required|integer',
                'book_journal_id' => 'required|integer',
                'description'     => 'required|string',
                'amount'          => 'required|numeric',
                'type_mutasi'     => 'required|string', // validasi sesuai kebutuhan
                'nilai_buku'      => 'required|numeric',
                'date'           => 'required|date',
                'code_group'     => 'required|numeric',
                'lawan_code_group' => 'required|numeric',
                'toko_id' => 'required|integer',
            ]);

            $codeGroupPrepaid = $request->input('code_group'); //ini pasti yang inventaris
            $lawanCodeGroup = $request->input('lawan_code_group');
            $description = $request->input('description');
            $amount = $request->input('amount');
            if ($amount < 0) {
                $amount = $amount * -1;
                $codeDebet = $lawanCodeGroup; //beban
                $codeKredit = $codeGroupPrepaid; //prepaid
            } else {
                $codeDebet = $codeGroupPrepaid; //prepaid
                $codeKredit = $lawanCodeGroup; //kas|hutang
            }
            if ($isOtomatisJurnal) {
                $kredits = [
                    [
                        'code_group' => $codeKredit,
                        'description' => $description,
                        'amount' => $amount,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $debets = [
                    [
                        'code_group' => $codeDebet,
                        'description' => $description,
                        'amount' => $amount,
                        'reference_id' => null,
                        'reference_type' => null,
                    ],
                ];
                $st = JournalController::createBaseJournal(new Request([
                    'kredits' => $kredits,
                    'debets' => $debets,
                    'type' => 'umum',
                    'date' => Date('Y-m-d H:i:s'),
                    'is_auto_generated' => 1,
                    'title' => 'pembelian inventaris',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) {
                    return $st;
                }
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->where('code_group', $codeGroupPrepaid)->first();
                $journalID = $journal->id;
            } else {
                $journalID = null;
                $number = null;
            }

            $validated['journal_id'] = $journalID;
            $validated['journal_number'] = $number;
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
