<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Invoice {{ $data->invoice_number }} - {{ $data->person->name }}
        <span class="fs-8 px-2 rounded-1 bg-primary text-white"> {{ getModel($data->person_type) }} </span>
    </h5>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            @if ($total_kartu == 0)
                <p>invoice ini bisa dibatalkan finalnya karena belum ada kartu</p>
                <button class="btn btn-danger" onclick="batalkanFinal('{{ $data->id }}')">Batalkan Final</button>
            @endif
        </div>
        <div class="col-xs-12 col-md-12">
            <h5>List Detail</h5>
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="bg-white text-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga </th>
                        <th>Diskon</th>
                        <th>Total</th>
                        <th>Aksi </th>
                    </tr>
                </thead>
                <tbody id="body-detail-invoice">
                    @foreach ($data['details'] as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->cutom_stock_name ? $item->custom_stock_name : $item->stock->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ format_price($item->price) }}</td>
                            <td>{{ format_price($item->discount) }}</td>
                            <td>{{ format_price($item->total_price) }}</td>
                            <td> <button class="btn btn-outline-info btn-sm"
                                    onclick="refresh('{{ $item->id }}','{{ $data->reference_model }}')">
                                    <i class="fas fa-refresh"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center">Total</td>
                        <td>{{ format_price($data->total_price) }}</td>
                    </tr>
            </table>
        </div>


        <div class="col-xs-12 col-md-12">
            <div class="card mt-4">
                <div class="card-body">
                    <p>status : {{ $data->status }}</p>

                    <div class="row">

                        <h6>Kartu Kartu </h6>

                        @foreach ($data['kartus'] as $key => $items)
                            <div class="col-xs-12">
                                <div class="bg-primary p-2 mb-2">
                                    <h6 class="text-white">{{ $key }}</h6>
                                    <div class="row text-white">
                                        @foreach ($items as $item)
                                            <div class="col-xs-12 col-md-4">
                                                <p>{{ $item->date }} -
                                                    <strong>{{ $item->code_group_name }}</strong> :
                                                    {{ format_price($item->amount_journal) }} <span
                                                        class="fs-8">[journal_id : {{ $item->journal_id }}, journal_number= {{$item['journal_number']}},
                                                        kartu_id= {{ $item->kartu_id }}]
                                                    
                                                    </span>
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="row" id="div-input">
                            @if ($data->reference_model == 'App\\Models\\InvoiceSaleDetail')
                                <div class="col-xs-12 ">
                                    <p class="">Tanggal </p>
                                    <input type="datetime-local" class="form-control" id="date-penjualan" />
                                    <p class="">Akun Persediaan</p>
                                    <select class="form-control" id="select-pcoa-persediaan"></select>
                                    <p class="">Akun (Piutang /kas )</p>
                                    <select class="form-control" id="select-pcoa-piutang-kas"></select>
                                    <p>Akun Penjualan</p>
                                    <select class="form-control" id="select-pcoa-penjualan"></select>
                                    <button class="btn btn-primary" onclick="createClaimPenjualan()">Claim
                                        Penjualan</button>
                                </div>
                            @elseif($data->reference_model == 'App\\Models\\InvoicePurchaseDetail')
                                <div class="col-xs-12 mt-1 ">
                                    <h5 class="text-primary-dark mb-1"> <a
                                            href="javascript:void(toggleDivMutasiPurchaseAll())"> <strong>Klaim
                                                pembelian Seluruhnya </strong>
                                            <i id="icon-create"
                                                class="bx bx-caret-down toggle-icon card-mutasi-purchase-all"></i>
                                        </a>
                                    </h5>

                                    <div id=""
                                        class="tree-toggle mb-3  card-mutasi-purchase-all bg-primary-lightest">
                                        <div class="bg-primary-lightest p-2 rounded-2 ">
                                            <p class="">Tanggal </p>
                                            <input type="datetime-local" class="form-control" id="date-pembelian" />
                                            <p class="">Akun Persediaan</p>
                                            <select class="form-control select-coa-persediaan"
                                                id="select-coa-persediaan"></select>
                                            <p class="">Lawan Akun (hutang /kas )</p>
                                            <select class="form-control select-coa-hutang-kas"
                                                id="select-coa-hutang-kas"></select>
                                            <button class="btn btn-primary"
                                                onclick="createClaimPembelian()">Submit</button>
                                        </div>
                                    </div>
                                </div>



                                <div class="col-xs-12 mt-2">
                                    <h5 class="text-primary-dark mb-1"> <a
                                            href="javascript:void(toggleDivMutasiPurchase())"> <strong>Klaim
                                                pembelian sebagian </strong>
                                            <i id="icon-create"
                                                class="bx bx-caret-down toggle-icon card-mutasi-purchase"></i> </a>
                                    </h5>

                                    <div id=""
                                        class="tree-toggle mb-3  card-mutasi-purchase bg-primary-lightest">
                                        <div class="row p-2">
                                            <div class="col-md-12 col-xs-12">
                                                @php $index=1; @endphp
                                                @foreach ($data['details'] as $key => $item)
                                                    <form id="form-mutasi-purchase{{ $index }}">
                                                        {{ csrf_field() }}
                                                        <input type="hidden" name="invoice_pack_number"
                                                            value="{{ $data->invoice_number }}" />
                                                        <input type="hidden" name="invoice_pack_id"
                                                            value="{{ $data->id }}" />
                                                        <input type="hidden" name="invoice_purchase_detail_id"
                                                            value="{{ $item->id }}" />
                                                        <div class="row pb-1 mt-1 parent-input-detail"
                                                            style="border-bottom:1px solid  rgb(0, 98.4, 204);">
                                                            <div class="col-md-3 col-xs-12">
                                                                <label>Tangal</label>
                                                                <input type="datetime-local" class="form-control"
                                                                    id="bdp-date" name="date"
                                                                    value="{{ $item->created_at ?? now }}" />
                                                            </div>

                                                            <div class="col-md-3 col-xs-12">
                                                                <label>Nama Barang</label>
                                                                <input type="text" class="form-control"
                                                                    id="bdp-stock_name"
                                                                    value="{{ $item->custom_stock_name ?? $item->stock->name }}"
                                                                    readonly />
                                                                <input type="hidden" id="bdp-stock_id" name="stock_id"
                                                                    value="{{ $item->stock_id }}" />
                                                            </div>


                                                            <div class="col-md-2 col-xs-12">
                                                                <label>Jumlah</label>
                                                                <input type="text" class="form-control detail-qty"
                                                                    onchange="updateHargaMutasiPurchase(this)"
                                                                    name="quantity"
                                                                    placeholder="qty bahan: {{ $item->quantity }}"
                                                                    id="bdp-quantity" />

                                                                <input type="hidden" class="detail-price"
                                                                    value="{{ $item->price }}" />
                                                            </div>

                                                            <div class="col-md-2 col-xs-12">
                                                                <label>Satuan</label>
                                                                <input class="form-control detail-unit" type="text"
                                                                    readonly name="unit" id="bdp-satuan"
                                                                    value="{{ $item->unit }}" />
                                                            </div>
                                                            <div class="col-md-2 col-xs-12">
                                                                <label>Nilai Mutasi</label>
                                                                <input class="form-control detail-nilai-mutasi "
                                                                    type="text" readonly name="nilai_mutasi"
                                                                    value="" />
                                                            </div>
                                                            <div class="col-md-3 col-xs-12">
                                                                <label>Akun Persediaan / Beban</Label>
                                                                <select
                                                                    class="select-coa-persediaan-beban form-control"
                                                                    name="code_group_debet">
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3 col-xs-12">
                                                                <label>Akun Hutang / Kas</Label>
                                                                <select class="select-coa-hutang-kas form-control"
                                                                    name="code_group_kredit">

                                                                </select>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label>Aksi</label><br>
                                                                <button type="button"
                                                                    onclick="submitMutasiPurchase('{{ $index }}')"
                                                                    class="mb-3 btn btn-primary">submit</button>
                                                            </div>

                                                        </div>
                                                    </form>
                                                    <!-- <hr class="text-black" style="z-index:100"></hr> -->
                                                    @php $index++; @endphp
                                                @endforeach


                                            </div>
                                            <div class="col-md-2 col-xs-12">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>

<script>
    $(document).ready(function() {
        initItemSelectManual('#select-pcoa-persediaan',
            '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan', 'chart account',
            '#global-modal #div-input');
        initItemSelectManual('#select-pcoa-piutang-kas',
            '{{ route('chart-account.get-item-keuangan') }}?kind=piutang|kas', 'chart account',
            '#global-modal #div-input');
        initItemSelectManual('#select-pcoa-penjualan',
            '{{ route('chart-account.get-item-keuangan') }}?kind=penjualan', 'chart account',
            '#global-modal #div-input');
        initItemSelectManual('.select-coa-persediaan',
            '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan', 'chart account',
            '#global-modal #div-input');
        initItemSelectManual('.select-coa-persediaan-beban',
            '{{ route('chart-account.get-item-keuangan') }}?kind=persediaan|beban', 'chart account',
            '#global-modal #div-input');
        initItemSelectManual('.select-coa-hutang-kas',
            '{{ route('chart-account.get-item-keuangan') }}?kind=hutang|kas', 'chart account',
            '#global-modal  #div-input');
    });


    function batalkanFinal(id) {
        swalConfirmAndSubmit({
            url: '{{ url('admin/invoice/invoice-cancel-final') }}',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            onSuccess: function(res) {
                if (res.status == 1) {
                    hideModal();
                } else {
                    Swal.fire('Opss', res.msg, 'warning');
                }
            },
        });
    }

    function refresh(id, model) {
        console.log(id, model);
        if (model == 'App\\Models\\InvoicePurchaseDetail') {
            url = '{{ url('admin/invoice/invoice-purchase-refresh') }}/' + id;
        } else {
            url = '{{ url('admin/invoice/invoice-sales-refresh') }}/' + id;
        }
        loading(1);
        $.ajax({
            url: url,
            method: 'get',
            success: function(res) {
                loading(0);
                if (res.status == 1) {
                    swalInfo('success', 'berhasil refresh', 'success');

                } else {
                    swalInfo()('error', res.msg, 'error');
                }
            },
            error: function(err) {
                loading(0);
                swalInfo('opps',
                    'something wrong', 'error'
                );
            }
        });
    }

    function updateHargaMutasiPurchase(elem) {
        let parent = $(elem).closest('.parent-input-detail');
        let qty = parent.find('.detail-qty').val();
        let price = parent.find('.detail-price').val();
        let total = qty * price;
        parent.find('.detail-nilai-mutasi').val(formatRupiah(total));
    }


    function toggleDivMutasiPurchase() {
        $('.card-mutasi-purchase').toggleClass('open');
        if ($('.card-mutasi-purchase').hasClass('open')) {
            // initAllItem();
        }
    }

    function toggleDivMutasiPurchaseAll() {
        $('.card-mutasi-purchase-all').toggleClass('open');
        if ($('.card-mutasi-purchase-all').hasClass('open')) {
            // initAllItem();
        }
    }

    function createClaimPenjualan() {

        swalConfirmAndSubmit({
            url: '{{ url(route('invoice.create-claim-penjualan')) }}',
            data: {
                coa_penjualan: $('#select-pcoa-penjualan').val(),
                coa_persediaan: $('#select-pcoa-persediaan').val(),
                coa_piutang_kas: $('#select-pcoa-piutang-kas').val(),
                date: $('#date-penjualan').val(),
                invoice_pack_id: '{{ $data->id }}',
                _token: '{{ csrf_token() }}'
            },
            onSuccess: function(res) {
                if (res.status == 1) {

                    hideModal();
                } else {
                    Swal.fire('Opss', res.msg, 'warning');
                }
            },
        });

    }



    function createClaimPembelian() {

        swalConfirmAndSubmit({
            url: '{{ url(route('invoice.create-claim-pembelian')) }}',
            data: {
                coa_persediaan: $('#select-coa-persediaan').val(),
                coa_hutang_kas: $('#select-coa-hutang-kas').val(),
                date: $('#date-pembelian').val(),
                invoice_pack_id: '{{ $data->id }}',
                _token: '{{ csrf_token() }}'
            },
            onSuccess: function(res) {
                if (res.status == 1) {
                    hideModal();
                } else {
                    Swal.fire('Opss', res.msg, 'warning');
                }
            },
        });

    }



    function submitMutasiPurchase(i) {
        swalConfirmAndSubmit({
            url: '{{ url('admin/invoice/purchase-create-mutations') }}',
            data: $('#form-mutasi-purchase' + i).serialize(),
            onSuccess: function(res) {
                console.log(res);

            },
        });
    }
</script>
