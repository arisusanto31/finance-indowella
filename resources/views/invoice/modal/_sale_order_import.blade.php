<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Import sales order </h5>
</div>
<div class="modal-body">

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <select id="select-toko" class="form-control">
            </select>
        </div>
    </div>


    <div class="table-responsive mt-2">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Number</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $row =>$sale)
                @php
                $jumlah= count($sale['details']);
                $id= $sale['id'];
                @endphp

                @foreach($sale['details'] as $detail)
                <tr>
                    <td rowspan="{{$jumlah}}">{{$row+1}}</td>
                    <td rowspan="{{$jumlah}}">{{$sale['package_number']}}</td>
                    <td rowspan="{{$jumlah}}">{{$detail['stock_name']}}</td>
                    <td>{{$detail['quantity']}}</td>
                    <td>{{$detail['unit']}}</td>
                    <td>{{$detail['price']}}</td>
                    <td>{{$detail['total_price']}}</td>
                    <td id="status{{$id}}">
                        <button class="btn btn-primary btn-sm" onclick="importData('{{$id}}')">
                            import
                        </button>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>

<script>
    console.log('masuk kok ini sale order import');
    initItemSelectManual('#select-toko', '{{route("toko.get-item")}}', 'pilih toko', '#global-modal');
    var allTrans = <?php echo json_encode($sales); ?>;
    allTrans = collect(allTrans).keyBy('id').all();


    function importData(id) {
        let data = allTrans[id];
        tokoid = $('#select-toko option:selected').val();
        if (tokoid == null || tokoid == undefined) {
            swalInfo('opps', 'tolong pilih toko', 'info');
            return 0;
        }
        let dataPost = {
            customer_name: data.customer_name,
            sales_order_number: data.package_number,
            reference_stock_id: data.details.map(item => item.stock_id),
            reference_stock_type: data.stock_type,
            quantity: data.details.map(item => item.quantity),
            price_unit: data.details.map(item => item.price),
            unit: data.details.map(item => item.unit),
            total_price: data.details.map(item => item.total_price),

            toko_id: tokoid,
            reference_id: data.id,
            reference_type: data.reference_type,
            _token: '{{csrf_token()}}'
        };
        console.log(dataPost);
        swalConfirmAndSubmit({
            url: '{{route("invoice.sales-order.store") }}',
            data: dataPost,
            onSuccess: function(res) {
                if (res.status == 1) {

                    $('#status' + id).html(`<i class="fas fa-check color-primary"></i> terimport`);
                }
            },
        });
    }
</script>