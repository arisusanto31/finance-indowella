<?php

namespace App\Models;

use App\Traits\HasIndexDate;
use App\Traits\HasModelSaldoUang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoicePurchaseDetail extends Model
{


    protected $fillable = [
        'invoice_pack_number',
        'invoice_pack_id',
        'book_journal_id',
        'stock_id',
        'quantity',
        'unit',
        'price',
        'total_price',
        'discount',
        'supplier_id',
        'custom_stock_name',
        'created_at',
        'is_ppn',
        'total_ppn_m',
        'factur_supplier_number',
        'fp_number',
        'kartu_stock_type',
        'kartu_stock_id',
        'index_date',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->custom_stock_name) {
                $stock = Stock::find($model->stock_id);
                if ($stock->name != 'custom') {
                    $model->custom_stock_name = $stock->name;
                }
            }
        });

        static::updating(function ($model) {
            if (!$model->custom_stock_name) {
                $stock = Stock::find($model->stock_id);
                if ($stock->name != 'custom') {
                    $model->custom_stock_name = $stock->name;
                }
            }
        });
    }


    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_purchase_details'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

    public function parent()
    {
        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
    }
    public function invoicePack()
    {
        return $this->belongsTo(InvoicePack::class, 'invoice_pack_number', 'invoice_number');
    }


    public function stock()

    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public static function getTotalMutasiKartu($date)
    {
        $dateAwal = createCarbon($date)->startOfMonth()->format('ymdHis000');
        $dateAkhir = createCarbon($date)->format('ymdHis999');
        $total = InvoicePurchaseDetail::query()->where('index_date', '>', $dateAwal)->where('index_date', '<', $dateAkhir)->sum('total_price');
        return $total ? $total : 0;
    }

    public static function getTotalMutasiJournal($date)
    {
        $dateAwal = createCarbon($date)->startOfMonth()->format('ymdHis00');
        $dateAkhir = createCarbon($date)->format('ymdHis99');
        $coastock= ChartAccountAlias::where('reference_model', KartuStock::class)->pluck('code_group')->all();
        $coaintransit= ChartAccountAlias::where('reference_model', KartuInTransit::class)->pluck('code_group')->all();
        $total = Journal::where('index_date', '>=', $dateAwal)->where('index_date', '<', $dateAkhir)->whereIn('code_group', $coastock)->where('amount_debet', '>', 0)->sum(DB::raw('amount_debet-amount_kredit'));
        $total+= Journal::where('index_date', '>=', $dateAwal)->where('index_date', '<', $dateAkhir)->whereIn('code_group', $coaintransit)->sum(DB::raw('amount_debet-amount_kredit'));
        return $total ? ($total) : 0;
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

    public function fillKartuStockID()
    {
        try {
            //cari dulu aja dari intransit. karena yang awal adalah intransit lur
            $ks = KartuInTransit::where('invoice_pack_number', $this->invoice_pack_number)
                ->where('stock_id', $this->stock_id)
                ->where('mutasi_rupiah_total', '>', 0)
                ->first();
            if (!$ks) {
                $ks = KartuStock::where('purchase_order_id', $this->id)->first();
            }
            if ($ks) {
                $ks->kartu_stock_id = $ks->id;
            }
            if (!$ks) {
                $ks = KartuStock::leftJoin('journals', 'journals.id', 'kartu_stocks.journal_id')
                    ->where('journals.book_journal_id', bookID())
                    ->where('kartu_stocks.stock_id', $this->stock_id)
                    ->where('journals.description', 'like', '%' . $this->invoice_pack_number . '%')
                    ->select('kartu_stocks.id as kartu_stock_id', 'kartu_stocks.mutasi_rupiah_total', 'journals.id as journal_id', 'journals.journal_number', 'journals.index_date_group')
                    ->get();
                if (count($ks) == 1) {
                    $ks = $ks->first();
                } else {
                    $ks = collect($ks)->where('mutasi_rupiah_total', $this->total_price)->first();
                }
            }
            if ($ks) {
                $theks = get_class($ks)::find($ks->kartu_stock_id);
                $theks->purchase_order_id = $this->id;
                $theks->save();
                $this->kartu_stock_id = $ks->kartu_stock_id;
                $this->kartu_stock_type = get_class($ks);
                $this->index_date = self::getNextIndexDate(Carbon::createFromFormat('ymdHis', $ks->index_date_group));
                info('update this index date ' . $this->index_date . ' for invoice purchase detail id ' . $this->id);
                $this->index_date_group = $ks->index_date_group;
                $this->journal_id = $ks->journal_id;
                $this->journal_number = $ks->journal_number;
                $this->save();
            }
        } catch (\Exception $e) {

            info('gagal isi kartu stock id untuk invoice purchase detail id ' . $this->id . ' dengan error ' . $e->getMessage());
            throw new \Exception('Gagal isi kartu stock id untuk invoice purchase detail id ' . $this->id . ' dengan error ' . $e->getMessage());
        }
    }
}
