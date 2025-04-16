<?php



namespace App\Models;

// use app/Models/InvoicePack.php
use Illuminate\Database\Eloquent\Model;

class InvoicePack extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'invoice_date',
        'total_price',
        'status',
        'bukti_file',
    ];

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceSaleDetail::class, 'invoice_number', 'invoice_number');
    }

    public function show($id)
{
    $invoice = InvoicePack::with('invoiceDetails')->findOrFail($id);
    return view('invoice.show', compact('invoice'));
}

public function invoiceDetails()
{
    return $this->hasMany(InvoiceSaleDetail::class, 'invoice_number', 'invoice_number');
}

}
