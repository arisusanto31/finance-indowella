<?php

namespace App\Models;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class KartuStock extends Model
{
    //



    protected static function booted()
    {

        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'journals'; // untuk dukung alias `j` kalau pakai from('journals as j')
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



    public static function create(Request $request)
    {
        $lock = Cache::lock('kartu-stock-' . $request->input('stock_id'), 40);
        try {

            $kartu = new KartuStock;
            $kartu->stock_id = $request->input('stock_id');
            $kartu->mutasi_qty_backend = $request->input('mutasi_qty_backend');
            $kartu->unit_backend = $request->input('unit_backend');
            $kartu->mutasi_quantity = $request->input('mutasi_quantity');
            $kartu->unit = $request->input('unit');
            $kartu->mutasi_rupiah_on_unit = $request->input('mutasi_rupiah_on_unit') ?? 0;
            $kartu->mutasi_rupiah_total = $request->input('mutasi_rupiah_total') ?? 0;
            //mutasi terakhir sebelum mutasi id yg diinput oleh user
            $lastCard = KartuStock::where('stock_id', $kartu->stock_id)->orderBy('id', 'desc')->first();
            if (!$lastCard) {
                $lastCard = new KartuStock;
                $lastCard->saldo_qty_backend = 0;
                $lastCard->saldo_rupiah_total = 0;
            }
            $kartu->book_journal_id = session('book_journal_id');
            $kartu->saldo_qty_backend =moneyAdd($lastCard->saldo_qty_backend, $kartu->mutasi_qty_backend);
            $kartu->saldo_rupiah_total = moneyAdd($lastCard->saldo_rupiah_total, $kartu->mutasi_rupiah_total);
            $kartu->save();
        } catch (LockTimeoutException $e) {
            info('kartu stock timeout on md' . $request->input('mutation_detail_id'));
            return [
                'status' => 0,
                'msg' => 'kartu stock timeout'
            ];
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
            'status' => 1,
            'msg' => $kartu
        ];
    }
}
