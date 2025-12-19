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
            <h5 class="text-primary-dark card-header"> <a href="javascript:void(openCardCreate())">⚒️ <strong>BUAT
                        PURCHASE
                        ORDER</strong>
                    <i id="icon-create" class="bx bx-caret-down toggle-icon"></i> </a>
            </h5>

            <div id="card-create" class="container tree-toggle">

                <div class="mb-3 mt-2">
                    <button type="button" class="btn btn-primary" onclick="addrow()" id="addDebit">+Tambah</button>
                    <button type="button" class="btn btn-primary" onclick="openImportDataExcel('{{ book()->id }}')"
                        id="btn-import">Import Excel</button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Supplier</label>
                        <select name="supplier_id" class="form-control select2-supplier" required></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nomor Invoice</label>
                        <input name="invoice_pack_number" type="text" class="form-control" required
                            placeholder="Nomor Invoice">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Invoice</label>
                        <input type="datetime-local" name="date" class="form-control" value="{{ now() }}" />
                    </div>
                </div>

                <div id="invoice-wrapper" class="debet-wrapper">
                </div>

                <hr>
                <div class="d-flex justify-content-end pe-4" style="margin-right:30px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" name ="is_ppn" type="checkbox" id="is-ppn" checked />
                        <label class="form-check-label" for="is-ppn"><i class="fas fa-hand-holding-usd"></i> Pembelian
                            PPN
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-end pe-4">
                    <div class="d-flex gap-2">
                        <label for="total_invoice" class="me-2 mt-1 fw-bold">TOTAL INVOICE</label>
                        <div class="d-flex flex-column">
                            <div class="relative-pos">
                                <div class="absolute-pos" style="left:10px;top:5px;"> net </div>
                                <input type="text" class="form-control text-end" autocomplete="off" readonly
                                    style="width: 200px;" id="total-invoice" readonly>
                            </div>

                            <div class="relative-pos div-ppn">
                                <div class="absolute-pos" style="left:10px;top:5px;"> PPN </div>
                                <input type="text" class="form-control text-end" autocomplete="off" readonly
                                    style="width: 200px;" id="total-ppn-m" readonly>
                            </div>
                            <div class="relative-pos div-ppn">
                                <div class="absolute-pos" style="left:10px;top:5px;"> gross </div>
                                <input type="text" class="form-control text-end" autocomplete="off" readonly
                                    style="width: 200px;" id="total-gross" readonly>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" onclick="submitInvoice()" class="btn btn-primary mb-3 w-100">Submit
                        Invoice</button>
                </div>
            </div>
        </div>
    </form>


    <div class="card mb-4 shadow p-3">


        <div class="text-primary-dark "> 📁 <strong>DAFTAR INVOICE</strong></div>
        <div class="d-flex justify-content pe-4 mb-3">
            <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                << </button>
                    <span class="badge bg-primary d-flex justify-content-center align-items-center">
                        {{ getListMonth()[$month] }} {{ $year }}</span>
                    <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()">
                        >>
                    </button>
        </div>

        <div class="d-flex flex-column bg-primary text-white p-2 rounded-2 mb-3" style="max-width:400px">
            <p class="mb-0">Total Invoice: <strong>Rp{{ format_price($totalInvoice) }}</strong></p>
            <p class="mb-0" id="total-final">Total Invoice Final:
                <strong>Rp{{ format_price($totalInvoiceFinal) }}</strong>
            </p>
            <p class="mb-0" id="total-mark">Total Invoice Mark:
                <strong>Rp{{ format_price($totalInvoiceMark) }}</strong>
            </p>
        </div>

        @if ($invoices->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>No</th>
                            <th>tanggal</th>
                            <th>Invoice</th>
                            <th>Supplier</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Harga Satuan</th>
                            <th>Diskon</th>
                            <th>Sub-Total</th>
                            <th>Total</th>
                            <th> Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                            $parent = [];
                        @endphp
                        @foreach ($invoices as $invoiceNumber => $items)
                            @php
                                // $theparent = $items->first()->parent;

                                // $parent[$theparent->id] = $theparent;
                                $rowspan = $items->count();
                                $invoiceSubtotal = $items->sum(
                                    fn($item) => $item->quantity * $item->price - $item->discount,
                                );
                            @endphp

                            @foreach ($items as $index => $item)
                                <tr id="TR{{ $item->invoice_pack_id }}"
                                    class="parent{{ $item->parent->id }} @if ($item->parent->is_mark == 1) bg-primary-lightest @endif">
                                    @if ($index === 0)
                                        <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                                        <td rowspan="{{ $rowspan }}">
                                            {{ createCarbon($item->created_at)->format('Y-m-d') }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $invoiceNumber }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $item->supplier->name ?? '-' }}</td>
                                    @endif

                                    <td>{{ $item->custom_stock_name != '??' ? $item->custom_stock_name : $item->stock->name }}
                                    </td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td class="text-end">Rp{{ number_format($item->price) }}</td>
                                    <td class="text-end">Rp{{ number_format($item->discount) }}</td>
                                    <td class="text-end">Rp{{ number_format($item->total_price) }}
                                        @if ($item->total_ppn_m > 0)
                                            <br>
                                            <div class="bg-success p-2 rounded-2 text-white " style="font-size:11px;">
                                                <i
                                                    class="fas fa-hand-holding-usd"></i>{{ format_price($item->total_ppn_m) }}
                                            </div>
                                        @endif
                                    </td>
                                    @if ($index === 0)
                                        <td rowspan="{{ $rowspan }}">
                                            <strong>Rp{{ number_format($item->parent->total_price) }}</strong>
                                            @if ($item->total_ppn_m > 0)
                                                <br>
                                                <div class="bg-success p-2 text-end rounded-2 text-white "
                                                    style="font-size:11px;">
                                                    <i
                                                        class="fas fa-hand-holding-usd"></i>{{ format_price($item->parent->total_ppn_m) }}
                                                </div>
                                            @endif

                                        </td>
                                        @if ($index === 0)
                                            <td id="action{{ $item->parent->id }}" rowspan="{{ $rowspan }}">
                                                @if ($item->parent->is_final == 1)
                                                    <a href="javascript:void(lihatDetailInvoice('{{ $item->invoice_pack_number }}'))"
                                                        class="btn btn-sm btn-outline-primary" title="Lihat Invoice">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                @if ($item->parent->is_final == 0)
                                                    <a href="javascript:void(makeFinal('{{ $item->invoice_pack_id }}'))"
                                                        class="btn btn-sm btn-outline-primary" title="make final"
                                                        id="btn-final{{ $item->invoice_pack_id }}">
                                                        <i class="fas fa-upload"></i>
                                                    </a>
                                                    <a href="javascript:void(editInvoicePurchase('{{ $item->invoice_pack_number }}'))"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(deleteInvoicePurchase('{{ $item->invoice_pack_id }}'))"
                                                        class="btn btn-sm btn-outline-danger" title="Hapus Invoice">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                @endif
                                                <a href="javascript:void(makeMark('{{ $item->parent->id }}'))"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-paw"></i>
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
            <div class="alert alert-warning text-center">
                Belum ada data invoice.
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
            initItemSelectManual('.select2-stock', '{{ url('admin/master/stock/get-item') }}', '-- Pilih Produk --');
            initItemSelectManual('.select2-supplier', '{{ url('admin/master/supplier/get-item') }}', '-- Pilih Supplier --');

            function lihatDetailInvoice(invoiceNumber) {
                showDetailOnModal('{{ url('admin/invoice/show-detail') }}/' + invoiceNumber, 'xl');
            }


             function openImportDataExcel(bookID) {
                showDetailOnModal('{{ url('admin/invoice/purchase-open-import-excel') }}/' + bookID, 'xl');
            }


            function editInvoicePurchase(invoiceNumber) {
                console.log("Edit Purchase Invoice:", invoiceNumber);
                showDetailOnModal('{{ url('/admin/invoice/invoice-purchase/edit') }}/' + invoiceNumber, 'xl');
            }

            function deleteInvoicePurchase(id) {
                swalDelete({
                    url: '{{ url('admin/invoice/delete-invoice-purchase') }}/' + id,
                    elem: '#TR' + id
                });
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

            function makeFinal(id) {
                swalConfirmAndSubmit({
                    url: '{{ url('admin/invoice/invoice-make-final') }}',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    onSuccess: function(res) {
                        html = ` <a href="javascript:void(lihatDetailInvoice('${res.msg.invoice_number}'))"
                                    class="btn btn-sm btn-outline-primary" title="Lihat Invoice">
                                    <i class="fas fa-eye"></i>
                                </a>
                                 <a href="javascript:void(makeMark('${res.msg.id}'))"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-paw"></i>
                                </a>
                                `;
                        $('#action' + id).html(html);
                        parents[id].is_final = 1;
                        updateTotalMarked();
                    },
                });
            }

            function makeMark(id) {
                $.ajax({
                    url: '{{ url('admin/invoice/invoice-mark') }}',
                    method: 'post',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        $('.parent' + id).addClass('bg-primary-lightest');
                        parents[id].is_mark = res.msg.is_mark;
                        updateTotalMarked();
                    },
                });
            }

            function updateTotalMarked() {
                totalMarked = collect(parents).where('is_mark', 1).sum('total_price');
                totalFinal = collect(parents).where('is_final', 1).sum('total_price');
                $('#total-mark').html('Total Invoice Mark: <strong>Rp' + formatRupiah(totalMarked) + '</strong>');
                $('#total-final').html('Total Invoice Final: <strong>Rp' + formatRupiah(totalFinal) + '</strong>');
                collect(parents).each(function(item) {
                    if (item.is_mark == 1)
                        $('.parent' + item.id).addClass('bg-primary-lightest');
                    if (item.is_mark == 0)
                        $('.parent' + item.id).removeClass('bg-primary-lightest');
                });
            }




            function submitInvoice() {
                swalConfirmAndSubmit({
                    url: '{{ route('invoice.purchase.store') }}',
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

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/invoice/invoice-purchase') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/invoice/invoice-purchase') }}?month=' + month + '&year=' + year;
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
                isPPN = $('#is-ppn').is(':checked');
                if (isPPN) {
                    PPN = totalInvoice * 0.11;
                    gross = totalInvoice + PPN;
                    $('#total-ppn-m').val(formatRupiah(PPN.toFixed(2)));
                    $('#total-gross').val(formatRupiah(gross.toFixed(2)));
                    $('.div-ppn').removeClass('hidden');
                } else {
                    $('#total-ppn-m').val(formatRupiah('0'));
                    $('#total-gross').val(formatRupiah(totalInvoice.toFixed(2)));
                    $('.div-ppn').addClass('hidden');
                }
            }

            $('#is-ppn').change(function() {
                updateTotalInvoice();
            });

            function updateStockUnit(el) {
                const card = el.closest('.rowdebet');
                const divStock = card.querySelector('.col-stock');
                const divCustomStock = card.querySelector('.col-custom-stock');
                const selectedOption = el.options[el.selectedIndex];
                const unitSelect = card.querySelector('.unit');
                const id = selectedOption.value;
                console.log('searching for ' + id);
                $.ajax({
                    url: '{{ url('admin/master/stock/get-unit') }}/' + id,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            if (res.stock.name == 'custom') {
                                divStock.classList.add('col-md-1');
                                divStock.classList.remove('col-md-3');
                                divCustomStock.classList.remove('hidden');
                            }
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
                        <div class="col-md-3 col-stock">
                            <label class="form-label">Nama Produk</label>
                            <select name="stock_id[]" onchange="updateStockUnit(this)" class="form-control select2-stock stock" required></select>
                        </div>
                        <div class="col-md-2 col-custom-stock hidden">
                            <label class="form-label">Nama Custom </label>
                            <input type="text" name="custom_stock_name[]" class="form-control"/>
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
                initItemSelectManual('.select2-stock', '{{ url('admin/master/stock/get-item') }}', '-- Pilih Produk --');


            }
            var parents = [];
            $(document).ready(function() {
                addrow();
                parents = {!! json_encode($parent) !!};

            });
        </script>
    @endpush
</x-app-layout>
