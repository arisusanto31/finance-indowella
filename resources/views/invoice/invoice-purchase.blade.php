<x-app-layout>
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form id="form-invoice">
        @csrf

        <div class="container py-4 p-3 mb-4 card shadow-sm">
            <h2>Create Invoice Purchase</h2>

            <div class="mb-3 mt-2">
                <button type="button" class="btn btn-success" onclick="addrow()" id="addDebit">+Tambah</button>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pilih Supplier</label>
                    <select name="supplier_id" class="form-control select2-supplier" required></select>
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

            <div id="invoice-wrapper" class="debet-wrapper">
            </div>

            <hr>
            <div class="d-flex justify-content-end pe-4">
                <div class="d-flex align-items-center gap-2">
                    <label for="total_invoice" class="me-2 fw-bold">TOTAL INVOICE</label>
                    <input type="text" class="form-control text-end" autocomplete="off" readonly style="width: 200px;" id="total-invoice" readonly>
                </div>
            </div>
            <div class="mt-4">
                <button type="button" onclick="submitInvoice()" class="btn btn-primary w-100">Submit Invoice</button>
            </div>
        </div>
    </form>

    @if ($invoices->isNotEmpty())
    <div class="card mb-4 shadow p-3">
        <table class="table table-bordered">
            <thead class="table-primary text-center">
                <tr>
                    <th>No</th>
                    <th>Invoice</th>
                    <th>Supplier</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Harga Satuan</th>
                    <th>Diskon</th>
                    <th>Sub-Total</th>
                    <th>Total</th>
                    <th> Aksi nya say</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($invoices as $invoiceNumber => $items)
                @php
                $rowspan = $items->count();
                $invoiceSubtotal = $items->sum(fn($item) => ($item->quantity * $item->price) - $item->discount);
                @endphp

                @foreach ($items as $index => $item)
                <tr>
                    @if ($index === 0)
                    <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $invoiceNumber }}</td>
                    <td rowspan="{{ $rowspan }}">{{ $item->supplier->name ?? '-' }}</td>
                    @endif

                    <td>{{ $item->stock->name ?? '-' }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">Rp{{ number_format($item->price) }}</td>
                    <td class="text-end">Rp{{ number_format($item->discount) }}</td>

                    @php
                    $subtotal = ($item->quantity * $item->price) - $item->discount;
                    @endphp
                    <td class="text-end">Rp{{ number_format($subtotal) }}</td>

                    @if ($index === 0)
                    <td rowspan="{{ $rowspan }}"><strong>Rp{{ number_format($invoiceSubtotal) }}</strong></td>
                    @if ($index === 0)
                    <td rowspan="{{ $rowspan }}">
                        <a href="javascript:void(lihatDetailInvoice('{{$item->invoice_number}}'))" class="btn btn-sm btn-outline-primary" title="Lihat Invoice">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="" class="btn btn-sm btn-outline-primary" title="Edit Invoice">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                    @endif

                    @endif
                </tr>
                @endforeach
                @endforeach

            </tbody>

        </table>
    </div>
    @else
    <div class="card mb-2 shadow p-3">
        <div class="alert alert-warning text-center">
            Belum ada data invoice.
        </div>
    </div>
    @endif

    @push('styles')
    <style>
        .btn-close-card {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 30px;
            height: 30px !important;
            background-color: red;
            border: none;
            border-radius: 50%;
            font-size: 23px;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            z-index: 10;
        }

        .centered-flex {
            display: flex;
            justify-content: center;
            /* Horizontal */
            align-items: center;
            height: 30px;
            width: 30px;
            /* Vertical */
            /* Kalau mau vertikal tengah terhadap layar penuh */
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        initItemSelectManual('.select2-stock', '{{ url("admin/master/stock/get-item") }}', '-- Pilih Produk --');
        initItemSelectManual('.select2-supplier', '{{ url("admin/master/supplier/get-item") }}', '-- Pilih Supplier --');

        function lihatDetailInvoice(invoiceNumber) {
            showDetailOnModal('{{url("admin/invoice/show-detail")}}/' + invoiceNumber, 'xl');
        }

        function removeDebetRow(btn) {
            const card = btn.closest('.rowdebet');
            const totalRows = document.querySelectorAll('.rowdebet').length;
            console.log(totalRows);
            if (totalRows > 1) {
                card.remove();
            } else {
                Swal.fire('Oops!', 'Minimal harus ada satu baris lur 😈!', 'warning');
            }
        }

        function submitInvoice() {
            swalConfirmAndSubmit({
                url: '{{route("invoice.purchase.store") }}',
                data: $('#form-invoice').serialize(),
                onSuccess: function(res) {
                    if (res.status == 1) {
                        window.location.reload();
                    }
                },
            });
        }

        function updateHarga(el) {
            const card = el.closest('.rowdebet');
            const quantity = card.querySelector('.quantity').value;
            const price = card.querySelector('.price').value;
            const discount = card.querySelector('.diskon').value;

            const subTotal = (quantity * price) - discount;
            card.querySelector('.sub-total').value = formatRupiah(subTotal.toFixed(2));
            updateTotalInvoice();
        }

        function updateTotalInvoice() {
            totalInvoice = 0;
            $('.sub-total').each(function() {
                const total = parseFloat(formatDB($(this).val()));
                if (!isNaN(total)) {
                    totalInvoice += total;
                }
            });
            $('#total-invoice').val(formatRupiah(totalInvoice.toFixed(2)));
        }

        function updateStockUnit(el) {
            const card = el.closest('.rowdebet');
            const selectedOption = el.options[el.selectedIndex];
            const unitSelect = card.querySelector('.unit');
            const id = selectedOption.value;
            console.log('searching for ' + id);
            $.ajax({
                url: '{{url("admin/master/stock/get-unit")}}/' + id,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        res.msg.forEach(function(item) {
                            html += `<option value="${item.unit}">${item.unit}</option>`;
                        });
                        unitSelect.innerHTML = html;
                    }
                },
                error: function(res) {

                }
            });
            const unit = selectedOption.getAttribute('data-unit');
            card.querySelector('.unit').value = unit;
        }


        function addrow() {
            newRow = `
                <div class="card border shadow-sm rounded p-3 mb-3 position-relative rowdebet">
                    <div type="button" class="btn-close-card" onclick="removeDebetRow(this)"><div class="centered-flex"><i class="fas fa-close"></i></div></div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Nama Produk</label>
                            <select name="stock_id[]" onchange="updateStockUnit(this)" class="form-control select2-stock stock" required></select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty</label>
                            <input name="quantity[]" onchange="updateHarga(this)" type="number" step="0.01" class="form-control quantity" placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Satuan</label>
                            <select name="unit[]" class="form-control unit">
                              
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga Satuan</label>
                            <input name="price_unit[]" type="text"  onchange="updateHarga(this)" step="0.01" class="form-control price" placeholder="Harga">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Diskon</label>
                            <input name="discount[]" type="text"  onchange="updateHarga(this)" class="form-control diskon" placeholder="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sub total</label>
                            <input name="total_price[]" type="text" class="form-control sub-total" readonly>
                        </div>
                    </div>
                </div>
                    `;
            $('#invoice-wrapper').append(newRow);
            initItemSelectManual('.select2-stock', '{{ url("admin/master/stock/get-item") }}', '-- Pilih Produk --');


        }

        $(document).ready(function() {
            addrow();

        });
    </script>
    @endpush
</x-app-layout>