<x-app-layout>
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <form id="form-invoice">
        @csrf
        <div class=" mb-4 card shadow-sm">
            <h5 class="text-primary-dark card-header"> <a href="javascript:void(openCardCreate())">⚒️ <strong>BUAT SALE
                        ORDER</strong>
                    <i id="icon-create" class="bx bx-caret-down toggle-icon"></i> </a>
            </h5>
            <div id="card-create" class="container tree-toggle">
                <div class="mb-3 mt-2">
                    <button type="button" class="btn btn-primary" onclick="addrow()" id="addDebit">+Tambah</button>
                    @if (book()->name == 'Buku Toko')
                    <button type="button" class="btn btn-primary" onclick="openImportData('{{ book()->id }}')"
                        id="btn-import">Import dari Toko</button>
                    @else
                    <button type="button" class="btn btn-primary" onclick="openImportData('{{ book()->id }}')"
                        id="btn-import">Import dari Manuf</button>
                    @endif
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Pilih Customer</label>
                        <select name="customer_id" class="form-control select2-customer" required></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pilih Toko</label>
                        <select name="toko_id" class="form-control select2-toko" required></select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nomor SO</label>
                        <input name="sales_order_number" type="text" class="form-control" required
                            placeholder="Nomor Invoice">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal SO</label>
                        <input type="text" class="form-control" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                    </div>
                </div>
                <div id="invoice-wrapper" class="debet-wrapper">
                </div>
                <hr>
                <div class="d-flex justify-content-end pe-4">
                    <div class="d-flex align-items-center gap-2">
                        <label for="total_invoice" class="me-2 fw-bold">TOTAL SO</label>
                        <input type="text" class="form-control text-end" autocomplete="off" readonly
                            style="width: 200px;" id="total-invoice" readonly>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="submitData()" class="btn btn-primary mb-3 w-100">Submit </button>
                </div>
            </div>
        </div>
    </form>



    <div class="card mb-4 shadow p-3">

        <div class="text-primary-dark "> 📁 <strong>DAFTAR SALE ORDER </strong> </div>
        <div class="d-flex justify-content pe-4 mb-3">
            <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                << </button>
                    <span class="badge bg-primary d-flex justify-content-center align-items-center"> {{getListMonth()[$month]}} {{$year}}</span>
                    <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()"> >></button>

        </div>

        @if($salesOrders->isNotEmpty())
        <div class="table-responsive mt-2">
            <table class="table table-bordered">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No</th>
                        <th>TGL</th>
                        <th>Nomer SO</th>
                        <th>Customer</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Harga Satuan</th>
                        <th>Diskon</th>
                        <th>Sub-Total</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach ($salesOrders as $invoiceNumber => $items)
                    @php
                    $rowspan = $items->count();
                    $invoiceSubtotal = $items->sum(fn($item) => ($item->quantity * $item->price) - $item->discount);
                    @endphp

                    @foreach ($items as $index => $item)
                    <tr>
                        @if ($index === 0)
                        <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->created_at->format('Y-m-d') }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $invoiceNumber }} </td>
                        <td rowspan="{{ $rowspan }}">{{ $item->customer->name ?? '-' }}</td>
                        @endif

                        <td>{{ $item->stock->name ?? '-' }}</td>
                        <td class="text-end">{{ format_price($item->quantity) }}</td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-end">Rp{{ format_price($item->price) }}</td>
                        <td class="text-end">Rp{{ format_price($item->discount) }}</td>

                        @php
                        $subtotal = ($item->quantity * $item->price) - $item->discount;
                        @endphp
                        <td class="text-end">Rp{{ format_price($subtotal) }}</td>

                        @if ($index === 0)

                        <td rowspan="{{ $rowspan }}"><strong>Rp{{ format_price($invoiceSubtotal) }}</strong>
                            @if($item->parent->ref_akun_cash_kind_name)
                            <br>
                            <div class="bg-primary p-2 rounded-2 text-white"><i class="fas fa-wallet"></i> {{$item->parent->ref_akun_cash_kind_name}}</div>
                            @endif

                        </td>
                        <td rowspan="{{ $rowspan }}">
                            <p class="colorblack text-center" style="width:100%;line-height:120%;"><strong>{{strtoupper($item->parent->status)}}</strong></p>



                            @php
                            $bgPayment = 'bglevel3';
                            if (preg_match('/^DP/', $item->parent->status_payment)) {
                            $bgPayment = 'bg-warning';
                            }
                            if (preg_match('/^LUNAS.*/', $item->parent->status_payment)) {
                            $bgPayment = 'bg-warning';
                            }
                            if ($item->parent->status_payment == 'LUNAS 100%') {
                            $bgPayment = 'bg-success';
                            }
                            if ($item->parent->status_payment == 'BELUM BAYAR') {
                            $bgPayment = 'bg-danger';
                            }

                            $bgDelivery = 'bglevel3';
                            if ($item->parent->status_delivery == 'Barang diproses') {
                            $bgDelivery = 'bg-danger';
                            }
                            if ($item->parent->status_delivery == 'Barang Ready') {
                            $bgDelivery = 'bg-warning';
                            }
                            if (preg_match('/^terkirim/', $item->parent->status_delivery)) {
                            $bgDelivery = 'bg-warning';
                            }
                            if ($item->parent->status_delivery == 'terkirim 100%') {
                            $bgDelivery = 'bg-success';
                            }
                            @endphp
                            <span class="badge {{ $bgPayment }}"> <i class="fas fa-wallet"></i>
                                {{ $item->parent->status_payment }}</span>
                            <span class="badge {{ $bgDelivery }}"> <i class="fas fa-truck"></i>
                                {{ $item->parent->status_delivery }}</span>
                        </td>
                        <td rowspan="{{ $rowspan }}">

                            @if($item->parent->is_final==1)
                            <a href="javascript:void(lihatDetailInvoice('{{$item->sales_order_number}}'))" class="btn btn-sm btn-outline-primary" title="Lihat Invoice">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endif
                            @if($item->parent->is_final==0)
                            <a href="javascript:void(makeFinal('{{$item->sales_order_id}}'))" class="btn btn-sm btn-outline-primary" title="make final" id="btn-final{{$item->sales_order_id}}">
                                <i class="fas fa-upload"></i>
                            </a>

                            <a href="javascript:void(editInvoice('{{$item->sales_order_number}}'))" class="btn btn-sm btn-outline-primary" title="Edit Invoice">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
        @else

        <div class="container p-0">
            <div class="alert alert-warning text-center">
                Belum ada data Sales order.
            </div>
        </div>

        @endif
    </div>








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
        initItemSelectManual('.select2-stock', '{{url("admin/master/stock/get-item") }}', '-- Pilih Produk --');
        initItemSelectManual('.select2-customer', '{{url("admin/master/customer/get-item") }}', '-- Pilih Customer --');
        initItemSelectManual('.select2-toko', '{{ url("admin/master/toko/get-item")}}', '-- Pilih Toko --');


        function lihatDetailInvoice(invoiceNumber) {
            showDetailOnModal('{{url("admin/invoice/show-sales-detail")}}/' + invoiceNumber, 'xl');
        }

        function prevMonth() {
            month = '{{$month}}';
            year = '{{$year}}';
            month--;
            if (month < 1) {
                month = 12;
                year--;
            }
            window.location.href = '{{url("admin/invoice/sales-order")}}?month=' + month + '&year=' + year;
        }

        function nextMonth() {
            month = '{{$month}}';
            year = '{{$year}}';
            month++;
            if (month > 12) {
                month = 1;
                year++;
            }
            window.location.href = '{{url("admin/invoice/sales-order")}}?month=' + month + '&year=' + year;
        }

        function lihatDetailInvoice(invoiceNumber) {
            showDetailOnModal('{{url("admin/invoice/show-sales-detail")}}/' + invoiceNumber, 'xl');
        }

        function openImportData(bookID) {
            showDetailOnModal('{{url("admin/invoice/sales-open-import")}}/' + bookID, 'xl');
        }

        function openCardCreate() {
            $('#card-create').toggleClass('open');
            $('#icon-create').toggleClass('open');
        }

        function makeFinal(id) {
            swalConfirmAndSubmit({
                url: '{{url("admin/invoice/sales-make-final")}}',
                data: {
                    id: id,
                    _token: '{{csrf_token()}}'
                },
                onSuccess: function(res) {
                    $('#btn-final' + id).remove();
                },
            });
        }

        function editInvoice(invoiceNumber) {
            console.log("edit" + invoiceNumber);
            showDetailOnModal('{{ url("/admin/invoice/edit-sales-order")}}/' + invoiceNumber, 'xl');
        }





        function openImportData(bookID) {
            showDetailOnModal('{{ url("admin/invoice/sales-open-import")}}/' + bookID, 'xl');
        }

        function openCardCreate() {
            $('#card-create').toggleClass('open');
            $('#icon-create').toggleClass('open');
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

        function submitData() {
            swalConfirmAndSubmit({
                url: '{{ route("invoice.sales-order.store") }}',
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
            initItemSelectManual('.select2-stock', '{{url("admin/master/stock/get-item") }}', '-- Pilih Produk --');


        }

        $(document).ready(function() {
            addrow();

        });
    </script>
    @endpush
</x-app-layout>