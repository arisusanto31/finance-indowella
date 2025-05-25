<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Import sales order </h5>

    <button
        type="button"
        class="btn-close position-absolute end-0 top-0 m-3"
        data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="row">
        <div class="col-md-3 col-xs-12">
            import data ke toko:
            <select id="select-toko" class="form-control">
            </select>
        </div>
        <div class="col-md-3 col-xs-12">
            import ke tanggal:
            <input type="date" id="select-date" class="form-control" />

        </div>
    </div>
    <div class="bglevel1 p-2">
        <div class="row">
            <div class="col-xs-12 col-md-12 ">
                <div class="d-flex  justify-content-end gap-2 " style="width: 100%;">
                    <div class="" style="width:20%">
                        <input class="form-control" id="customer" placeholder="customer" />
                    </div>
                    <div class="" style="width:20%">
                        <select class="select-toko form-control" id="select-search-toko">
                        </select>
                    </div>
                    <div class="" style="width:20%">
                        <select class="select-coa-kas form-control" id="select-search-cashkind">
                        </select>
                    </div>
                    <div class="" style="width:15%">
                        <select class="form-control" id="select-search-date">
                            <option value="" disabled> pilih bulan </option>
                            @foreach(getListMonthYear() as $monthYear)
                            <option value="{{$monthYear}}">{{$monthYear}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="" style="width:15%">
                        <button style="width:100%" onclick="getImportData()"> search</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="table-responsive mt-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>TGL</th>
                            <th>Number</th>
                            <th>Toko </th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga</th>
                            <th>Total</th>
                            <th>Akun Bayar </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
<script src="https://cdn.jsdelivr.net/npm/collect.js@4.33.0/build/collect.min.js"></script>

<script>
    console.log('masuk kok ini sale order import');
    initItemSelectManual('#select-toko', '{{route("toko.get-item")}}', 'pilih toko', '#global-modal');
    initItemSelectManual('.select-coa-kas', '{{route("chart-account.get-item-keuangan")}}?kind=kas', 'Pilih Akun Kas', '#global-modal');
    var allTrans = [];
    getImportData();

    function updateSelectToko(data) {
        allToko = collect(data).map((itema) => {
            return collect(itema.details).pluck('toko').unique().all();
        }).all()
        fixToko = collect([]);
        allToko.forEach(function(darray) {
            fixToko = fixToko.merge(darray);
        });
        fixToko = collect(fixToko).unique().all();
        option = "";
        fixToko.forEach(function(item) {
            option += `<option value="${item}">${item}</option>`;
        });
        $('#select-search-toko').html(option);

    }

    function getImportData() {
        id = "{{book()->id}}";
        let monthyear = $('#select-search-date option:selected').val();
        let customer = $('#customer').val();
        let toko = $('#select-search-toko option:selected').val();
        if (toko == undefined) toko = "";
        $('#table-body').html("");
        $.ajax({
            url: '{{url("admin/invoice/sales-get-data-import")}}/' + id + '?monthyear=' + monthyear + '&&toko=' + toko + '&customer=' + customer,
            method: 'get',
            success: function(res) {
                console.log(res);
                html = "";
                if (toko == "") {
                    updateSelectToko(res.msg);
                }
                res.msg.forEach(function(item, i) {
                    allTrans[item.id] = item;
                    jumlah = item.details.length;

                    item.details.forEach(function(detail, j) {
                        html += `
                                <tr>
                                    ${j==0?`
                                    <td rowspan="${jumlah}">${i+1}</td>
                                    <td rowspan="${jumlah}">${formatNormalDate(new Date(item.created_at))}</td>
                                    <td rowspan="${jumlah}">${item.package_number} (${item.customer_name})</td>
                                    <td rowspan="${jumlah}">${detail.toko}</td>
                                    `:''}
                                    <td>${detail.stock_name}</td>
                                    <td>${detail['quantity']}</td>
                                    <td>${detail['unit']}</td>
                                    <td>${detail['price']}</td>
                                    <td>${detail['total_price']}</td>
                                    ${j==0?`
                                    <td rowspan="${jumlah}">${item.akun_cash_kind_name}</td>
                                    <td rowspan="${jumlah}" id="status${item.id}">
                                        <button class="btn btn-primary btn-sm" onclick="importData('${item.id}')">
                                            import
                                        </button>
                                    </td>
                                    `:''}
                                </tr>`;
                    });

                    $('#table-body').html(html);
                });

            },
            error: function(res) {

            }
        });
    }


    function importData(id) {
        let data = allTrans[id];
        tokoid = $('#select-toko option:selected').val();
        if (tokoid == null || tokoid == undefined) {
            swalInfo('opps', 'tolong pilih toko', 'info');
            return 0;
        }
        date = $('#select-date').val();
        if (date == null || date == undefined) {
            swalInfo('opps', 'tolong pilih tanggal import', 'info');
            return 0;
        }
        date = normalizeDate(date);

        let dataPost = {
            created_at: date,
            customer_name: data.customer_name,
            sales_order_number: data.package_number,
            custom_stock_name: data.details.map(item => item.stock_name),
            reference_stock_id: data.details.map(item => item.stock_id),
            reference_stock_type: data.stock_type,
            quantity: data.details.map(item => item.quantity),
            price_unit: data.details.map(item => item.price),
            unit: data.details.map(item => item.unit),
            total_price: data.details.map(item => item.total_price),
            akun_cash_kind_name: data.akun_cash_kind_name,
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