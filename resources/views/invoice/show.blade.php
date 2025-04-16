<h2>Invoice: {{ $invoice->invoice_number }}</h2>
<p>Customer: {{ $invoice->customer_id }}</p>
<p>Tanggal: {{ $invoice->invoice_date }}</p>

<h4>Daftar Barang:</h4>
<table border="1">
    <thead>
        <tr>
            <th>Stock ID</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoice->invoiceDetails as $item)
        <tr>
            <td>{{ $item->stock_id }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->price) }}</td>
            <td>{{ number_format($item->total_price) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

