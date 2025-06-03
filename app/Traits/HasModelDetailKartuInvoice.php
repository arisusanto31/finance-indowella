<?php

namespace App\Traits;

use App\Models\DetailKartuInvoice;
use App\Models\InvoicePack;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

trait HasModelDetailKartuInvoice
{

    public function createDetailKartuInvoice()
    {
        $kartu = $this;
        $saleOrderNumber = null;
        $invoiceNumber = null;
        $purchaseOrderNumber = null;
        $POID = $SOID = $invID = null;
        if (isset($kartu->purchase_order_number)) {
            $purchaseOrderNumber = $kartu->purchase_order_number;
            $POID = $kartu->purchase_order_id;
        }
        if (isset($kartu->sales_order_number)) {
            $saleOrderNumber = $kartu->sales_order_number;
            $salesOrder=SalesOrder::where('sales_order_number', $saleOrderNumber)->first();
            $SOID =$salesOrder ? $salesOrder->id : null;
        }
        if (isset($kartu->invoice_pack_number)) {
            $invoiceNumber = $kartu->invoice_pack_number;
            $invoice= InvoicePack::where('invoice_number', $invoiceNumber)->first();
            $invID =$invoice ? $invoice->id : null;
        }

        $dks = DetailKartuInvoice::storeData(new Request([
            'kartu_type' => get_class($kartu),
            'kartu_id' => $kartu->id,
            'journal_id' => $kartu->journal_id,
            'sales_order_number' => $saleOrderNumber,
            'sales_order_id' => $SOID,
            'purchase_order_number' => $purchaseOrderNumber,
            'purchase_order_id' > $POID,
            'invoice_pack_number' => $invoiceNumber,
            'invoice_pack_id' => $invID,

        ]));
        if ($dks['status'] == 0) {
            return $dks;
        }
        return ['status' => 1, 'msg' => $kartu];
    }

    public function isHasKartuInvoice()
    {
        $detail =  DetailKartuInvoice::where('kartu_type', get_class($this))
            ->where('kartu_id', $this->id)->first();
        return $detail ? true : false;
    }
}
