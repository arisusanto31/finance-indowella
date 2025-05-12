<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Sales Order {{$data->sales_order_number}} - {{$data->customer->name}} <span class="fs-8 px-2 rounded-1 bg-primary text-white">status: {{$data->status}} </span></h5>
    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal"
        aria-label="Close"></button>

</div>
<div class="modal-body">
    <div class="row">
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
                    </tr>
                </thead>
                <tbody id="body-detail-invoice">
                    @foreach($data['details'] as $key => $item)
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$item->stock->name}}</td>
                        <td>{{$item->quantity}}</td>
                        <td>{{format_price($item->price)}}</td>
                        <td>{{format_price($item->discount)}}</td>
                        <td>{{format_price($item->total_price)}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center">Total</td>
                        <td>{{format_price($data->total_price)}}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="col-xs-12 col-md-12">
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="text-primary-dark mb-2"> <strong>Resume Total </strong>
                        @if(count($data['resume_total'])>0)
                        @foreach($data['resume_total'] as $key => $total)
                        <p class="mb-0 mt-2 pb-2" style="font-size:15px; "> <i class="fas fa-circle"></i> {{$key}}</p>
                        <p class="mb-0 pb-2 fs-7 ps-3"> {{format_price($total)}}</p>
                        @endforeach
                        @endif
                </div>
            </div>
        </div>


        <div class="col-xs-12 col-md-12">
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivUangMuka())"> <strong>buat uang muka penjualan</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-uang-muka"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle card-uang-muka" style="height:80px ;">
                        <div class="row">
                            <div class="col-md-3 col-xs-12">
                                <label>deskripsi jurnal</label>
                                <input type="text" class="form-control" placeholder="deskripsi" id="uangmuka-description" />
                            </div>

                            <div class="col-md-3 col-xs-12">
                                <label>Jumlah</label>
                                <input type="text" class="form-control" placeholder="nilai pembayaran" id="uangmuka-amount" />
                            </div>

                            <div class="col-md-3 col-xs-12">
                                <label>Lawan Akun</label>
                                <select class="select-coa-kas form-control" id="uangmuka-lawanakun">
                                </select>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <label>Aksi</label>
                                <br>
                                <button onclick="submitUangMuka()" class="btn btn-primary">submit</button>
                            </div>
                        </div>

                    </div>
                    <h5 class="text-primary-dark mb-1"> <a href="javascript:void(toggleDivBDP())"> <strong>buat barang dalam proses </strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-bdp"></i> </a>
                    </h5>

                    <div id="" class="tree-toggle mb-3  card-bdp bg-primary-lightest">
                        <div class="row p-2">
                            <div class="col-md-12 col-xs-12">
                                <form id="form-bdp">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sales_order_number" value="{{$data->sales_order_number}}" />
                                    <input type="hidden" name="sales_order_id" value="{{$data->id}}" />
                                    @foreach($data['details'] as $key => $item)
                                    <div class="row">
                                        <div class="col-md-3 col-xs-12">
                                            <label>Nama Barang</label>
                                            <input type="text" class="form-control" id="bdp-stock_name" value="{{$item->stock->name}}" readonly />
                                            <input type="hidden" id="bdp-stock_id" name="stock_id[]" value="{{$item->stock_id}}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Flow</label>
                                            <select class="form-control" id="bdp-flow" name="flow[]">
                                                <option value="0">Masuk</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 col-xs-12">
                                            <label>Jumlah</label>
                                            <input type="text" class="form-control" name="quantity[]" placeholder="quantity" id="bdp-quantity" />
                                        </div>

                                        <div class="col-md-3 col-xs-12">
                                            <label>Satuan</label>
                                            <input class="form-control" type="text" readonly name="unit[]" id="bdp-satuan" value="{{$item->unit}}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Dari Akun </Label>
                                            <select class="select-coa-persediaan form-control" name="lawan_code_group[]">

                                            </select>
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Dari Barang Nomer? <span class="fs-7">(isi jika perlu)</span> </Label>
                                            <input type="text" class="form-control" name="spk_number[]" placeholder="number" id="bdp-spk_number" />
                                        </div>

                                    </div>
                                    <hr>
                                    @endforeach
                                </form>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <button onclick="submitBDP()" class="mb-3 btn btn-primary">submit</button>
                            </div>
                        </div>


                    </div>
                    <h5 class="text-primary-dark "> <a href="javascript:void(toggleDivBahanJadi())"> <strong>buat bahan jadi</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-bahan-jadi"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle card-bahan-jadi bg-primary-lightest">
                        <div class="row p-2">
                            <div class="col-md-12 col-xs-12">
                                <form id="form-bahan-jadi">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sales_order_number" value="{{$data->sales_order_number}}" />
                                    <input type="hidden" name="sales_order_id" value="{{$data->id}}" />
                                    @foreach($data['details'] as $key => $item)
                                    <div class="row">
                                        <div class="col-md-3 col-xs-12">
                                            <label>Nama Barang</label>
                                            <input type="hidden" id="bahan-jadi-stock_id" name="stock_id[]" value="{{$item->stock_id}}" />
                                            <input type="text" class="form-control" id="bahan-jadi-stock_name" value="{{$item->stock->name}}" readonly />

                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Nama Custom</label>
                                            <input type="text" class="form-control" id="bahan-jadi-custom_name" name="custom_stock_name[]" value="{{$item->stock->name}}" />

                                        </div>

                                        <div class="col-md-2 col-xs-12">
                                            <label>Flow</label>
                                            <select class="form-control" id="bdp-flow" name="flow[]">
                                                <option value="0">Masuk</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2 col-xs-12">
                                            <label>Jumlah</label>
                                            <input type="text" class="form-control" name="quantity[]" placeholder="quantity" id="bahan-jadi-quantity" />
                                        </div>

                                        <div class="col-md-2 col-xs-12">
                                            <label>Satuan</label>
                                            <input class="form-control" type="text" readonly name="unit[]" id="bahan-jadi-satuan" value="{{$item->unit}}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Dari Akun </Label>
                                            <select class="select-coa-persediaan form-control" name="lawan_code_group[]">

                                            </select>
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Dari Barang Nomer? <span class="fs-7">(isi jika perlu)</span> </Label>
                                            <input type="text" class="form-control" name="spk_number[]" placeholder="number" id="bahan-jadi-spk_number" />
                                        </div>


                                    </div>
                                    <hr>
                                    @endforeach
                                </form>
                                <div class="col-md-2 col-xs-12">
                                    <button onclick="submitBahanJadi()" class="btn btn-primary">submit</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivInvoice())"> <strong>buat Invoice</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-invoice"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle card-invoice bg-primary-lightest">
                        <div class="row p-2">
                            <div class="col-md-12 col-xs-12">
                                <form id="form-invoice">
                                    {{csrf_field()}}
                                    <input type="hidden" name="sales_order_number" value="{{$data->sales_order_number}}" />
                                    <input type="hidden" name="sales_order_id" value="{{$data->id}}" />

                                    @foreach($data['details'] as $key => $item)
                                    <div class="row">
                                        <div class="col-md-3 col-xs-12">
                                            <label>Nama Barang</label>
                                            <input type="text" class="form-control" id="invoice-stock_name" value="{{$item->stock->name}}" readonly />
                                            <input type="hidden" id="invoice-stock_id" value="{{$item->stock_id}}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Jumlah</label>
                                            <input type="text" class="form-control" placeholder="jumlah qty " id="invoice-quantity" />
                                        </div>

                                        <div class="col-md-3 col-xs-12">
                                            <label>Satuan</label>
                                            <input class="form-control" type="text" readonly id="invoice-unit" value="{{$item->unit}}" />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Akun Penjualan</label>
                                            <select class="form-control select-coa-penjualan" type="text" name="code_group_penjualan[]"></select>
                                        </div>

                                        <div class="col-md-5 col-xs-12">
                                            <label>Barang jadi</label>
                                            <input type="text" class="form-control" id="invoice-ket-barang-jadi{{$item->id}}" value="" readonly />
                                        </div>
                                        <div class="col-md-3 col-xs-12">
                                            <label>Pembiayaan HPP</label>
                                            <input type="text" class="form-control" placeholder="pembiayaan hpp" name="hpp[]" />
                                        </div>
                                    </div>
                                    <hr>
                                    @endforeach
                                </form>
                                <div class="col-md-2 col-xs-12">
                                    <button onclick="submitInvoice()" class="btn btn-primary">submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5 class="text-primary-dark"> <a href="javascript:void(toggleDivBayarInvoice())"> <strong>buat pembayaran invoice</strong>
                            <i id="icon-create" class="bx bx-caret-down toggle-icon card-bayar-invoice"></i> </a>
                    </h5>
                    <div id="" class="tree-toggle card-bayar-invoice">
                        <div class="row">
                            <div class="col-md-2 col-xs-12">
                                <label>Nomer Invoice</label>
                                <select class="form-control" id="bayar-invoice-invoice_number">

                                </select>
                            </div>

                            <div class="col-md-2 col-xs-12">
                                <label>Jumlah</label>
                                <input type="text" class="form-control" placeholder="nilai pembayaran" id="bayar-invoice-amount" />
                            </div>


                            <div class="col-md-3 col-xs-12">
                                <label>Akun Pembayaran</label>
                                <select class="select-coa-kas-uangmuka form-control" id="bayar-invoice-akun">
                                </select>
                            </div>
                            <div class="col-md-2 col-xs-12">
                                <label>Aksi</label>
                                <br>
                                <button onclick="submitBayarInvoice()" class="btn btn-primary">submit</button>
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        <h6>Kartu Kartu </h6>
                        @if(count($data['kartus'])>0)
                        @foreach($data['kartus'] as $key => $items)

                        <div class="col-xs-12">
                            <div class="bg-primary p-2 mb-2">
                                <h6 class="text-white">{{$key}}</h6>
                                <div class="row text-white">
                                    @foreach($items as $item)
                                    <div class="col-xs-12 col-md-4">
                                        <p>{{$item->created_at}} - <strong>{{$item->code_group_name}}</strong> : {{format_price($item->amount_journal)}} <span class="fs-8">[journal_id : {{$item->journal_id}}, kartu_id= {{$item->kartu_id}}]</span></p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="row">
                            @if($data->reference_model=='App\\Models\\InvoiceSaleDetail')
                            <div class="col-xs-12">
                                <p>Akun Persediaan</p>
                                <select class="form-control" id="select-pcoa-persediaan"></select>
                                <p>Akun (Piutang /kas )</p>
                                <select class="form-control" id="select-pcoa-piutang-kas"></select>
                                <p>Akun Penjualan</p>
                                <select class="form-control" id="select-pcoa-penjualan"></select>
                                <button class="btn btn-primary" onclick="createClaimPenjualan()">Claim Penjualan</button>
                            </div>
                            @elseif($data->reference_model=='App\\Models\\InvoicePurchaseDetail')
                            <div class="col-xs-12">
                                <p>Akun Persediaan</p>
                                <select class="form-control select-coa-persediaan"></select>
                                <p>Lawan Akun (hutang /kas )</p>
                                <select class="form-control" id="select-coa-hutang-kas"></select>
                                <button class="btn btn-primary" onclick="createClaimPembelian()">Claim Pembelian</button>
                            </div>
                            @endif
                        </div>
                        @endif
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
        initItemSelectManual('#select-pcoa-persediaan', '{{route("chart-account.get-item-keuangan")}}?kind=persediaan', '- pilih akun -', '#global-modal');
        initItemSelectManual('#select-pcoa-piutang-kas', '{{route("chart-account.get-item-keuangan")}}?kind=piutang|kas', '- pilih akun -', '#global-modal');

        initItemSelectManual('#select-pcoa-penjualan', '{{route("chart-account.get-item-keuangan")}}?kind=penjualan', '- pilih akun -', '#global-modal');
        initItemSelectManual('.select-coa-persediaan', '{{route("chart-account.get-item-keuangan")}}?kind=persediaan', '- pilih akun -', '#global-modal');
        initItemSelectManual('#select-coa-hutang-kas', '{{route("chart-account.get-item-keuangan")}}?kind=hutang|kas', '- pilih akun -', '#global-modal');
        initItemSelectManual('.select-coa-kas', '{{route("chart-account.get-item-keuangan")}}?kind=kas', '- pilih akun -', '#global-modal');
        initItemSelectManual('.select-coa-penjualan', '{{route("chart-account.get-item-keuangan")}}?kind=penjualan', '- pilih akun -', '#global-modal');

    });

    function toggleDivUangMuka() {
        $('.card-uang-muka').toggleClass('open');
    }

    function toggleDivBDP() {
        $('.card-bdp').toggleClass('open');
    }

    function toggleDivBahanJadi() {
        $('.card-bahan-jadi').toggleClass('open');
    }

    function toggleDivInvoice() {
        $('.card-invoice').toggleClass('open');
        if ($('.card-invoice').hasClass('open')) {
            updateInputInvoice('{{$data->sales_order_number}}');
        }
    }

    function toggleDivBayarInvoice() {
        $('.card-bayar-invoice').toggleClass('open');
    }


    function updateInputInvoice(number) {
        $.ajax({
            url: '{{url("admin/invoice/update-input-invoice")}}/' + number,
            method: 'get',
            data: {
                sales_order_number: number
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    res.msg.forEach(function eachItem(item) {
                        bahanJadi = res.bahan_jadi[item.stock_id];
                        html = `${bahanJadi.custom_stock_name} : ${bahanJadi.saldo_qty_backend/bahanJadi.mutasi_qty_backend*bahanJadi.mutasi_quantity} ${bahanJadi.unit} = ${formatRupiah(bahanJadi.saldo_rupiah_total)} `;
                        $('#invoice-ket-barang-jadi' + item.id).val(html)
                    });
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitUangMuka() {
        let description = $('#uangmuka-description').val();
        let amount = formatDB($('#uangmuka-amount').val());
        let lawanakun = $('#uangmuka-lawanakun option:selected').val();
        let factur = '{{$data->sales_order_number}}';
        $.ajax({
            url: '{{url("admin/kartu/kartu-dp-sales/create-mutation")}}',
            method: 'post',
            data: {
                description: description,
                amount_mutasi: amount,
                lawan_code_group: lawanakun,
                sales_order_number: factur,
                person_id: '{{$data->customer_id}}',
                person_type: '{{get_class($data->customer)}}',
                code_group: 214000,
                is_otomatis_jurnal: 1,
                _token: '{{csrf_token()}}'
            },
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil masuk', 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitBDP() {
        $.ajax({
            url: '{{url("admin/kartu/kartu-bdp/create-mutations")}}',
            method: 'post',
            data: $('#form-bdp').serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil masuk', 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitBahanJadi() {
        $.ajax({
            url: '{{url("admin/kartu/kartu-bahan-jadi/create-mutations")}}',
            method: 'post',
            data: $('#form-bahan-jadi').serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil masuk', 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }

    function submitInvoice(){
        $.ajax({
            url: '{{url("admin/invoice/create-invoices")}}',
            method: 'post',
            data: $('#form-invoice').serialize(),
            success: function(res) {
                console.log(res);
                if (res.status == 1) {
                    Swal.fire('success', 'berhasil masuk', 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('ops', 'something error ' + res.msg, 'error');
                }
            },
            error: function(res) {
                Swal.fire("opps", "something error", 'error');
            }
        });
    }
</script>