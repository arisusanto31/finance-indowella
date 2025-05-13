<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    //
    protected $table = 'sales_orders';

    protected $fillable = [
        'book_journal_id',
        'sales_order_number',
        'toko_id',
        'customer_id',
        'total_price',
        'status',
        'reference_id',
        'reference_type',

    ];

    public function details()
    {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_number', 'sales_order_number');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }

    public function detailKartuInvoices()
    {

        return $this->hasMany(DetailKartuInvoice::class, 'sales_order_id', 'id');
    }

    public function getTotalKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['code_group_name'] = $val->journal->chartAccount->name;
            $kartu = $val->kartu_type::find($val->kartu_id);
            if (isset($kartu->amount)) {
                $val['total'] = $kartu->amount;
            } else if (isset($kartu->amount_debet)) {
                $val['total'] = $kartu->amount_debet - $kartu->amount_kredit;
            } else if (isset($kartu->total_price)) {
                $val['total'] = $kartu->total_price;
            } else if (isset($kartu->mutasi_rupiah_total)) {
                $val['total'] = $kartu->mutasi_rupiah_total;
            }

            return $val;
        })->groupBy('code_group_name')->map(function ($val) {
            return $val->sum('total');
        });

        return $kartus ?? [];
    }
    public function getAllKartu()
    {
        $kartus = collect($this->detailKartuInvoices)->map(function ($val) {
            $val['type_kartu'] = $val->kartu_type ? explode('\\', $val->kartu_type)[2] : 'Kartu lain-lain';
            $val['code_group_name'] = $val->journal->chartAccount->name;
            $val['type_flow'] = $val->journal->amount_debet > 0 ? 'debet' : 'kredit';
            return $val;
        })->groupBy('type_kartu')->map(function ($vals) {
            return $vals->groupBy('type_flow')->all();
        })->all();


        return $kartus ?? [];
    }
}
