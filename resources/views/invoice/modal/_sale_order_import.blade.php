<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Import sales order </h5>

    <button type="button" id="btn-close-modal" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="bglevel1 p-2 mb-2">
        <div class="form-check form-switch">
            <input class="form-check-input" onchange="changeCustomImport()" type="checkbox" id="is-custom-import" />
            <label class="form-check-label" for="is-custom-import">Import Custom</label>
        </div>
        <div class="row hidden" id="div-custom-import">
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
    </div>
    <div class="bglevel1 p-2 mt-3">
        <div class="row">
            <div class="col-xs-12 col-md-12 ">
                <div class="d-flex  justify-content-end gap-2 " style="width: 100%;">

                    <div class="p-2" style="width:8%;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is-ppn" checked />
                            <label class="form-check-label" for="is-ppn">PPN</label>
                        </div>
                    </div>

                    <div class="" style="width:20%">
                        <input class="form-control" id="customer" placeholder="customer" />
                    </div>
                    <div class="" style="width:15%">
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
                            @foreach (getListMonthYear() as $monthYear)
                                <option value="{{ $monthYear }}">{{ $monthYear }}</option>
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
            <div class="col-md-12 hidden" id="resume-selected">
                <div>
                    <b style="font-size:22px;"> Total Selected : <span id="total-selected">0</span> </b>
                    <button onclick="importAllChecked()" class="btn btn-primary">import all</button>
                </div>
                <div style="width:300px;" class="mt-2 hidden" id="div-progressbar">
                    <div class="progress progress-modern mb-3">
                        <div class="progress-bar" id="progress-import" role="progressbar" style="width: 65%;">
                            65%
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll(this)" />
                            </th>
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
    initItemSelectManual('#select-toko', '{{ route('toko.get-item') }}', 'pilih toko', '#global-modal');
    initItemSelectManual('.select-coa-kas', '{{ route('chart-account.get-item-keuangan') }}?kind=kas', 'Pilih Akun Kas',
        '#global-modal');
    var allTrans = [];

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
        option += `<option value="">All</option>`;

        fixToko.forEach(function(item) {
            option += `<option value="${item}">${item}</option>`;
        });
        $('#select-search-toko').html(option);

    }

    function getImportData() {
        loading(1);
        id = "{{ book()->id }}";
        let monthyear = $('#select-search-date option:selected').val();
        let customer = $('#customer').val();
        let toko = $('#select-search-toko option:selected').val();
        let isPPN = $('#is-ppn').is(':checked') ? 1 : 0;
        if (toko == undefined) toko = "";
        $('#table-body').html("");
        $.ajax({
            url: '{{ url('admin/invoice/sales-get-data-import') }}/' + id + '?monthyear=' + monthyear +
                '&toko=' + toko + '&customer=' + customer + '&is_ppn=' + isPPN,
            method: 'get',
            success: function(res) {
                loading(0);
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
                                    <td rowspan="${jumlah}">
                                        <input type="checkbox" class="select-row-checkbox" data-id="${item.id}" onchange="updateTotalSelected()" />
                                    </td>
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
                                    <td rowspan="${jumlah}"> ${formatRupiah(collect(item.details).sum('total_price'))}<br>${item.akun_cash_kind_name}</td>
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
                loading(0);

            }
        });
    }

    function updateTotalSelected() {
        let countSelected = $('.select-row-checkbox:checked').length;
        if (countSelected > 0) {
            $('#resume-selected').removeClass('hidden');
        } else {
            $('#resume-selected').addClass('hidden');
        }
        let totalSelected = 0;
        $('.select-row-checkbox:checked').each(function() {
            let id = $(this).data('id');
            let data = allTrans[id];
            totalSelected += collect(data.details).sum('total_price');
        });
        $('#total-selected').html(formatRupiah(totalSelected));
    }


    function toggleSelectAll() {
        let isChecked = $('#select-all-checkbox').is(':checked');
        $('.select-row-checkbox').prop('checked', isChecked);
        updateTotalSelected();
    }



    async function importAllChecked() {
        totalCount = $('.select-row-checkbox:checked').length;
        if (totalCount == 0) {
            swalInfo('opps', 'tidak ada data yang dipilih', 'info');
            return;
        }
        $('#div-progressbar').removeClass('hidden');
        $('#progress-import').css('width', '0%');
        $('#progress-import').html('0%');
        $('#btn-close-modal').prop('disabled', true);
        totalProgress = 0;
        elems = $('.select-row-checkbox:checked');

        for (const el of elems) {
            let id = $(el).data('id');
            await importData(id);
            totalProgress++;
            let progressPercent = Math.round((totalProgress / totalCount) * 100);
            $('#progress-import').css('width', progressPercent + '%');
            $('#progress-import').html(progressPercent + '%');

        }
        $('#btn-close-modal').prop('disabled', false);
        setTimeout(() => {
            $('#div-progressbar').addClass('hidden');
        }, 2000);
    }

    function changeCustomImport() {
        let isChecked = $('#is-custom-import').is(':checked');
        if (isChecked) {
            $('#div-custom-import').removeClass('hidden');
        } else {
            $('#div-custom-import').addClass('hidden');
        }
    }



    // function importData(id) {
    //     let data = allTrans[id];
    //     tokoid = $('#select-toko option:selected').val();
    //     if (tokoid == null || tokoid == undefined) {
    //         swalInfo('opps', 'tolong pilih toko', 'info');
    //         return 0;
    //     }
    //     date = $('#select-date').val();
    //     if (date == null || date == undefined) {
    //         swalInfo('opps', 'tolong pilih tanggal import', 'info');
    //         return 0;
    //     }
    //     let dataPost = {
    //         created_at: date,
    //         customer_name: data.customer_name,
    //         sales_order_number: data.package_number,
    //         custom_stock_name: data.details.map(item => item.stock_name),
    //         reference_stock_id: data.details.map(item => item.stock_id),
    //         reference_stock_type: data.stock_type,
    //         quantity: data.details.map(item => item.quantity),
    //         qtyjadi: data.details.map(item => item.qtyjadi),
    //         price_unit: data.details.map(item => item.price),
    //         pricejadi: data.details.map(item => item.pricejadi),
    //         unit: data.details.map(item => item.unit),
    //         unitjadi: data.details.map(item => item.unitjadi),
    //         total_price: data.details.map(item => item.total_price),
    //         akun_cash_kind_name: data.akun_cash_kind_name,
    //         toko_id: tokoid,
    //         detail_reference_id: data.details.map(item => item.reference_id),
    //         detail_reference_type: data.details.map(item => item.reference_type),
    //         reference_id: data.id,
    //         reference_type: data.reference_type,
    //         _token: '{{ csrf_token() }}'
    //     };
    //     console.log(dataPost);
    //     swalConfirmAndSubmit({
    //         url: '{{ route('invoice.sales-order.store') }}',
    //         data: dataPost,
    //         onSuccess: function(res) {
    //             if (res.status == 1) {
    //                 $('#status' + id).html(`<i class="fas fa-check color-primary"></i> terimport`);
    //             }
    //         },
    //     });
    // }

    var tokoParents = @json($toko_parents);

    function importData(id) {

        return new Promise((resolve, reject) => {
            let data = allTrans[id];
            var date = null;
            var tokoid = null;
            isCustomImport = $('#is-custom-import').is(':checked');
            if (isCustomImport) {
                 tokoid = $('#select-toko option:selected').val();
                if (!tokoid) {
                    swalInfo('opps', 'tolong pilih toko', 'info');
                    return reject(new Error('Toko belum dipilih'));
                }
                date = $('#select-date').val();
                if (!date) {
                    swalInfo('opps', 'tolong pilih tanggal import', 'info');
                    return reject(new Error('Tanggal belum dipilih'));
                }
            } else {
                tokoid = null;
                date = null;
                //cek dulu jangan2 ada toko_id ynang belum ada linknya 
                if (tokoParents[data.toko_id] == undefined || tokoParents[data.toko_id] == null) {
                    swalInfo('opps', `Toko ${data.toko_id} belum ada link ke parent toko`, 'info');
                    return reject(new Error('Toko belum ada link ke parent toko'));
                }


            }

            let dataPost = {
                created_at: date ? date : data.created_at,
                customer_name: data.customer_name,
                sales_order_number: data.package_number,
                custom_stock_name: data.details.map(item => item.stock_name),
                reference_stock_id: data.details.map(item => item.stock_id),
                reference_stock_type: data.stock_type,
                quantity: data.details.map(item => item.quantity),
                qtyjadi: data.details.map(item => item.qtyjadi),
                price_unit: data.details.map(item => item.price),
                pricejadi: data.details.map(item => item.pricejadi),
                unit: data.details.map(item => item.unit),
                unitjadi: data.details.map(item => item.unitjadi),
                total_price: data.details.map(item => item.total_price),
                akun_cash_kind_name: data.akun_cash_kind_name,
                toko_id: tokoid ? tokoid : tokoParents[data.toko_id],
                detail_reference_id: data.details.map(item => item.reference_id),
                detail_reference_type: data.details.map(item => item.reference_type),
                reference_id: data.id,
                reference_type: data.reference_type,
                _token: '{{ csrf_token() }}'
            };

            console.log(dataPost);
            $.ajax({
                url: '{{ route('invoice.sales-order.store') }}',
                data: dataPost,
                method: 'post',
                success: function(res) {
                    if (res.status == 1) {
                        $('#status' + id).html(
                            `<i class="fas fa-check color-primary"></i> terimport`);
                    }
                    resolve(res); // kasih tahu "await" kalau sudah selesai
                },
                error: function(err) {
                    reject(err); // biar ketangkep di try/catch
                }
            });

        });
    }
</script>
