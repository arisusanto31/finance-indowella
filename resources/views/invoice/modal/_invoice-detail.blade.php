<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Detail Invoice {{$data->invoice_number}} - {{$data->person->name}} <span class="fs-8 px-2 rounded-1 bg-primary text-white"> {{getModel($data->person_type)}} </span></h5>
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
            </table>
        </div>


        <div class="col-xs-12 col-md-12">
            <div class="card mt-4">
                <div class="card-body">
                    <p>status : {{$data->status}}</p>

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
                                <select class="form-control" id="select-coa-persediaan"></select>
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
        initItemSelectManual('#select-pcoa-persediaan', '{{route("chart-account.get-item-keuangan")}}?kind=persediaan', 'chart account', '#global-modal');
        initItemSelectManual('#select-pcoa-piutang-kas', '{{route("chart-account.get-item-keuangan")}}?kind=piutang|kas', 'chart account', '#global-modal');
        initItemSelectManual('#select-pcoa-penjualan', '{{route("chart-account.get-item-keuangan")}}?kind=penjualan', 'chart account', '#global-modal');
    });

    function createClaimPenjualan() {
        $.ajax({
            url: '{{url(route("invoice.create-claim-penjualan"))}}',
            method: 'post',
            data: {
                coa_penjualan: $('#select-pcoa-penjualan').val(),
                coa_persediaan: $('#select-pcoa-persediaan').val(),
                coa_piutang_kas: $('#select-pcoa-piutang-kas').val(),
                invoice_pack_id: '{{$data->id}}',
                _token: '{{csrf_token()}}'
            },
            success: function(res) {
                if (res.status == 1) {
                    Swal.fire('Berhasil', res.msg, 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('Opss', res.msg, 'warning');
                }
            },
            error: function() {
                Swal.fire('Opss', 'something error', 'warning');
            }
        });
    }

    function createClaimPembelian() {
        $.ajax({
            url: '{{url(route("invoice.create-claim-pembelian"))}}',
            method: 'post',
            data: {
                coa_persediaan: $('#select-coa-persediaan').val(),
                coa_hutang_kas: $('#select-coa-hutang-kas').val(),
                invoice_pack_id: '{{$data->id}}',
                _token: '{{csrf_token()}}'
            },
            success: function(res) {
                if (res.status == 1) {
                    Swal.fire('Berhasil', res.msg, 'success');
                    $('#global-modal').modal('hide');
                } else {
                    Swal.fire('Opss', res.msg, 'warning');
                }
            },
            error: function() {
                Swal.fire('Opss', 'something error', 'warning');
            }
        });
    }
</script>