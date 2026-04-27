<?php

namespace App\Models;

use App\Traits\HasIndexDate;
use App\Traits\HasModelDetailKartuInvoice;
use App\Traits\HasModelSaldoUang;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceSaleDetail extends Model
{

    use HasModelDetailKartuInvoice;
    protected $fillable = [
        'row_index',
        'invoice_pack_number',
        'invoice_pack_id',
        'sales_order_number',
        'sales_order_id',
        'book_journal_id',
        'stock_id',
        'custom_stock_name',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'customer_id',
        'toko_id',
        'reference_id',
        'reference_type',
        'created_at',
        'is_ppn',
        'total_ppn_k',
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_sale_details'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

        static::updating(function ($model) {});
    }

    public static function getNextIndexDate($inputDate)
    {
        $date = createCarbon($inputDate)->format('ymdHis');

        $lastData = static::query()->where('index_date_group', $date)
            ->select(DB::raw('MAX(index_date) as maxindex'))
            ->first();
        info('last index date from ' . $date . ' : ' . ($lastData ? $lastData->maxindex : 'null'));

        $lastIndex = $lastData && $lastData->maxindex ? ((int) substr($lastData->maxindex, -3)) : 0;

        $newIndex = $date . str_pad($lastIndex + 1, 3, '0', STR_PAD_LEFT);

        return $newIndex;
    }
    public function parent()
    {

        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
    }
    public function stock()

    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    public static function getTotalMutasiKartu($date)
    {
        $dateAwal = createCarbon($date)->startOfMonth()->format('ymdHis000');
        $dateAkhir = createCarbon($date)->format('ymdHis999');
        $total = InvoiceSaleDetail::query()->where('index_date', '>', $dateAwal)->where('index_date', '<', $dateAkhir)->sum('total_price');
        return $total ? $total : 0;
    }

    public static function getTotalMutasiJounal($date)
    {
        $dateAwal = createCarbon($date)->startOfMonth()->format('ymdHis00');
        $dateAkhir = createCarbon($date)->format('ymdHis99');
        $coa = ChartAccount::where('reference_model', InvoiceSaleDetail::class)->pluck('code_group')->all();
        $total = Journal::where('index_date', '>', $dateAwal)->where('index_date', '<', $dateAkhir)->whereIn('code_group', $coa)->sum(DB::raw('amount_debet-amount_kredit'));
        return $total ? $total * -1 : 0;
    }

    public function fillIndexDate()
    {

        if ($this->journal_id > 0) {
            $journal  = Journal::find($this->journal_id);
            if ($journal) {
                $this->index_date_group = $journal->index_date_group;
                $this->index_date = self::getNextIndexDate($journal->created_at);
                $this->save();
            }
        }
    }
}
