<?php

namespace App\Models;

use App\Http\Controllers\JournalController;
use App\Traits\HasIndexDate;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Str;

class KartuInventory extends Model
{
    //
    use HasIndexDate;
    protected $table = "kartu_inventories";
    public $timestamps = true;
    public $fillable = [
        'book_journal_id',
        'inventory_id',
        'amount',
        'date',
        'type_mutasi',
        'nilai_buku',
        'code_group',
        'lawan_code_group',
        'description',
        'journal_id',
        'journal_number',
        'code_group_name',
        'toko_id',
    ];


    protected static function booted()
    {

        static::addGlobalScope('book_journal', function ($query) {
            $from = $query->getQuery()->from ?? 'kartu_inventories'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

        $invID = $request->input('inventory_id');
        $lock = Cache::lock('inventory-' . $invID, 30);
        //diini kita ambil dulu kartu yang lama
        try {
            $lock->block(15);
            $date = $request->input('date') ?? now();
            self::proteksiBackdate($date);
            $indexDate = self::getNextIndexDate($date);
            $lastKartu = KartuInventory::where('inventory_id', $invID)->where('index_date', '<', $indexDate)->orderBy('index_date', 'desc')->first();
            $lastNilaiBuku = $lastKartu ? $lastKartu->nilai_buku : 0;
            // Format nilai amount terlebih dahulu
            //input amount harus pakai languange indonesia
            $formattedAmount = $request->input('type_mutasi') == 'penyusutan' ? format_db($request->input('amount')) * -1 : format_db($request->input('amount'));
            $isOtomatisJurnal = $request->input('is_otomatis_jurnal');

            $request->merge([
                'index_date' => $indexDate,
                'index_date_group' => createCarbon($date)->format('ymdHis'),
                'book_journal_id' => bookID(),
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
                'inventory_id'    => 'required|integer',
                'description'   => 'required|string',
                'book_journal_id' => 'required|integer',
                'amount'          => 'required|numeric',
                'type_mutasi'     => 'required|string', // validasi sesuai kebutuhan
                'nilai_buku'      => 'required|numeric',
                'date'           => 'required|date',
                'code_group'      => 'required|integer',
                'lawan_code_group' => 'required|integer',
                'toko_id' => 'required|integer',
            ]);


            //dari sini kita bisa bikin jurnalnya dulu ini
            $codeGroupInventory = $request->input('code_group'); //ini pasti yang inventaris
            $lawanCodeGroup = $request->input('lawan_code_group');
            $description = $request->input('description');
            $amount = $request->input('amount');
            if ($amount < 0) {
                $amount = $amount * -1;
                $codeDebet = $lawanCodeGroup; //beban
                $codeKredit = $codeGroupInventory; //akumulasi susut
            } else {
                $codeDebet = $codeGroupInventory; //inventaris
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

                    'date' => $date,
                    'is_backdate' => self::isBackdate($date),
                    'is_auto_generated' => 1,
                    'title' => 'pembelian inventaris',
                    'url_try_again' => null

                ]), false);
                if ($st['status'] == 0) {
                    return $st;
                }
                $number = $st['journal_number'];
                $journal = Journal::where('journal_number', $number)->where('code_group', $codeGroupInventory)->first();
                $journalID = $journal->id;
            } else {
                $journalID = null;
                $number = null;
            }


            $validated['journal_id'] = $journalID;
            $validated['journal_number'] = $number;

            $ki = KartuInventory::create($validated);
            if (self::isBackdate($date)) {
                $ki->recalculateSaldo();
            }
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

    public function recalculateSaldo()
    {
        // Recalculate nilai buku based on the latest amount and previous nilai buku
        $kartus = KartuInventory::where('inventory_id', $this->inventory_id)
            ->where('index_date', '>', $this->index_date)->get();
        $nilaiBuku = $this->nilai_buku;
        foreach ($kartus as $kartu) {
            $realAmount = $kartu->type_mutasi == 'penyusutan' ? $kartu->amount * -1 : $kartu->amount;
            $kartu->nilai_buku = $nilaiBuku + $realAmount;
            $kartu->save();
            $nilaiBuku = $kartu->nilai_buku;
        }
    }
}
