<x-app-layout>
@if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('invoice.sales.store') }}">
        @csrf

        <div class="container py-4 p-3 mb-4 card shadow-sm">
            <h2>Create Invoice Sales ya</h2>

            <div class="mb-3 mt-2">
                <button type="button" class="btn btn-success" id="addDebit">+Tambah</button>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pilih Customer</label>
                    <select name="customer_id" class="form-control select2-customer" required></select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nomor Invoice</label>
                    <input name="invoice_number" type="text" class="form-control" required placeholder="Nomor Invoice">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Invoice</label>
                    <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                </div>
            </div>
            
            

            <div id="div-debet" class="debet-wrapper">
                <div class="card border shadow-sm rounded p-3 mb-3 position-relative rowdebet">
                    <button type="button" class="btn-close-card" onclick="removeDebetRow(this)">×</button>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="stock_id[]" class="form-control select2-stock" required></select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty</label>
                            <input name="quantity[]" type="number" step="0.01" class="form-control" placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <select name="unit[]" class="form-control">
                                <option value="pcs">PCS</option>
                                <option value="slop">Slop</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Satuan</label>
                            <input name="price_unit[]" type="number" step="0.01" class="form-control" placeholder="Harga">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Harga</label>
                            <input name="total_price[]" type="number" step="0.01" class="form-control" placeholder="Total">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Diskon</label>
                            <input name="discount[]" type="number" class="form-control" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <hr>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100">Submit Invoice</button>
            </div>
        </div>
    </form>
    @forelse ($invoices as $invoiceNumber => $items)
    <div class="card mb-4 shadow p-3">
        <h5>Invoice: {{ $invoiceNumber }}</h5>
        <p>Customer: {{ $items->first()->customer->name ?? '-' }}</p>
        <p>Tanggal: {{ $items->first()->created_at->format('Y-m-d') }}</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Harga</th>
                    <th>Diskon</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->stock->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>Rp{{ number_format($item->price) }}</td>
                        <td>Rp{{ number_format($item->discount) }}</td>
                        <td>Rp{{ number_format($item->total_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="alert alert-warning">Belum ada data invoice.</div>
@endforelse
    </tbody>
</table>


    </div>
@empty
    <div class="alert alert-warning text-center">
        Belum ada data invoice.
    </div>
@endforelse

    </tbody>
</table>

    @push('styles')
        <style>
            .btn-close-card {
                position: absolute;
                top: -12px;
                right: -12px;
                width: 30px;
                height: 30px;
                background-color: red;
                border: none;
                border-radius: 50%;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
                color: #fff;
                z-index: 10;
            }
        </style>
    @endpush

    @push('scripts')
    <script>
        function initSelectProdukAjax(context = document) {
            $(context).find('.select2-stock').select2({
                placeholder: '-- Pilih Produk --',
                allowClear: true,
                ajax: {
                    url: '{{ route("admin.stock.produk-get-item") }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        }
    
        function initSelectCustomerAjax(context = document) {
            $(context).find('.select2-customer').select2({
                placeholder: '-- Pilih Customer --',
                allowClear: true,
                ajax: {
                    url: '{{ route("customer.get-item") }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                }
            });
        }
    
        function removeDebetRow(btn) {
            const card = btn.closest('.rowdebet');
            const totalRows = document.querySelectorAll('.rowdebet').length;
            if (totalRows > 1) {
                card.remove();
            } else {
                Swal.fire('Oops!', 'Minimal harus ada satu baris lur hehe!', 'warning');
            }
        }
    
        $(document).ready(function () {
            initSelectProdukAjax();
            initSelectCustomerAjax();
    
            document.getElementById('addDebit').addEventListener('click', function () {
                const debitWrapper = document.getElementById('div-debet');
                const newRow = document.createElement('div');
                newRow.classList.add('card', 'border', 'shadow-sm', 'rounded', 'p-3', 'mb-3', 'position-relative', 'rowdebet');
    
                newRow.innerHTML = `
                    <button type="button" class="btn-close-card" onclick="removeDebetRow(this)">×</button>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="stock_id[]" class="form-control select2-stock" required></select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty</label>
                            <input name="quantity[]" type="number" step="0.01" class="form-control" placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <select name="unit[]" class="form-control">
                                <option value="pcs">PCS</option>
                                <option value="slop">Slop</option>
                                <option value="pack">Pack</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Satuan</label>
                            <input name="price_unit[]" type="number" step="0.01" class="form-control" placeholder="Harga">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Harga</label>
                            <input name="total_price[]" type="number" step="0.01" class="form-control" placeholder="Total">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Diskon</label>
                            <input name="discount[]" type="number" class="form-control" placeholder="0">
                        </div>
                    </div>
                `;
    
                debitWrapper.appendChild(newRow);
                initSelectProdukAjax(newRow);
            });
        });
    </script>
    @endpush
    
    
</x-app-layout>
