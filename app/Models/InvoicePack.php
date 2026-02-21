<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InvoiceSaleDetail;
use App\Models\Customer;
use App\Traits\HasModelChilds;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class InvoicePack extends Model
{
    use HasModelChilds;
    protected $table = 'invoice_packs';
    protected $fillable = [
        'invoice_number',
        'book_journal_id',
        'person_id',
        'person_type',
        'reference_model',
        'sales_order_id',
        'invoice_date',
        'total_price',
        'status',
        'total_ppn_k',
        'total_ppn_m',
        'toko_id',
        'reference_id',
        'reference_type',
        'created_at',
        'is_ppn',
        'total_ppn_k',
        'total_ppn_m',
        'prosen_pembayaran',
        'prosen_mutasi',
        'surat_jalan',
        'factur_supplier_number',
        'fp_number',
    ];

    protected static function booted()
    {
        static::addGlobalScope('journal', function ($query) {
            $from = $query->getQuery()->from ?? 'invoice_packs'; // untuk dukung alias `j` kalau pakai from('journals as j')
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

    public static function cekAvail($code, $count)
    {
        $invoice = InvoicePack::where('invoice_number', $code . $count)->first();
        if ($invoice) {
            return ['status' => 0, 'msg' => 'Invoice number already exists'];
        }
        return ['status' => 1, 'msg' => 'Invoice number available'];
    }
    public function invoiceDetails()
    {
        return $this->getChilds('invoice_pack_id');
    }


    public function person()
    {
        return $this->morphTo();
    }

    public function detailKartuInvoices()
    {

        return $this->hasMany(DetailKartuInvoice::class, 'invoice_pack_id', 'id');
    }

    public function getAllKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['type_kartu'] = $val->kartu_type ? explode('\\', $val->kartu_type)[2] : 'Kartu lain-lain';
            $val['code_group_name'] = $val->journal->chartAccount->name;
            $val['date'] = $val->journal->created_at->format('Y-m-d H:i:s');
            $val['journal_number'] = $val->journal->journal_number;

            return $val;
        })->groupBy('type_kartu');
        return $kartus ?? [];
    }

    public function getCodeFix()
    {
        $data = $this;
        if ($data->index == null) {
            //menandakan bahwa invoice ini belum pernah dapat fix code
            $personType = $data->person_type;
            $personID = $data->person_id;
            $inv = InvoicePack::where('is_final', 1)->where('person_id', $personID)
                ->where('person_type', $personType)
                ->orderBy('index', 'desc')->first();
            if ($inv) {
                $count = $inv->index + 1;
            } else {
                $count = 1;
            }
            $this->index = $count;
            if ($data->reference_model == InvoiceSaleDetail::class)
                $code = 'INV-S' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
            else if ($data->reference_model == InvoicePurchaseDetail::class)
                $code = 'INV-P' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
            else
                $code = 'INV-' . date('y') . '-' . toDigit($personID, 4) . '-' . toDigit($count, 4);
            return $code;
        }
        return $data->invoice_number;
    }

    public function updateStatus()
    {
        $journals = DetailKartuInvoice::where('invoice_pack_number', $this->invoice_number)
            ->select(
                DB::raw('sum(amount_journal) as total_journal'),
                'account_code_group',
                DB::raw('count(id) as total_count'),
                DB::raw('case WHEN account_code_group between 110000 and 120000 then "Kas"
                         WHEN account_code_group between 140000 and 150000 then "Persediaan" 
                         ELSE "Lain-lain" END as type_account')
            )->groupBy('account_code_group')->get()->keyBy('type_account')->all();
        // throw new \Exception(json_encode($journals));
        $persediaan = array_key_exists('Persediaan', $journals) ? $journals['Persediaan'] : null;
        $kas = array_key_exists('Kas', $journals) ? $journals['Kas'] : null;
        $totalMutasi = 0;
        $totalBayar = 0;
        if ($persediaan) {
            $totalMutasi = $persediaan->total_journal;
            if ($totalMutasi >= $this->total_price) {
                $totalMutasi = 100;
            } else {
                $totalMutasi = ($totalMutasi / $this->total_price) * 100;
            }
        }
        if ($kas) {
            $totalBayar = abs($kas->total_journal);
            if ($totalBayar >= $this->total_price) {
                $totalBayar = 100;
            } else {
                $totalBayar = ($totalBayar / $this->total_price) * 100;
            }
        }
        $this->prosen_pembayaran = $totalBayar;
        $this->prosen_mutasi = $totalMutasi;

        $this->status = $this->is_final == 1 ? 'FINAL' : 'DRAFT';
        $this->save();
    }
}
