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
                            <option value="" selected>- </option>
                            @foreach ($listParentsToko as $tokoname => $parentids)
                                <option value="{{ $parentids }}">{{ $tokoname }}</option>
                            @endforeach
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
            <div class="col-md-8 ">
                <div id="resume-selected" class="hidden">
                    <div>
                        <b style="font-size:22px;"> Total Selected : <span id="total-selected">0</span> </b>
                        <button onclick="importAllChecked(0)" class="btn btn-primary">import all NON</button>
                        <button onclick="importAllChecked(1)" class="btn btn-primary">import all PPN</button>
                    </div>
                    <div style="width:300px;" class="mt-2 hidden" id="div-progressbar">
                        <div class="progress progress-modern mb-3">
                            <div class="progress-bar" id="progress-import" role="progressbar" style="width: 65%;">
                                65%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-end">
                <div id="div-page">

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
    <button type="button" id="btn-close-modal2" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
<script src="https://cdn.jsdelivr.net/npm/collect.js@4.33.0/build/collect.min.js"></script>

<script>
    console.log('masuk kok ini sale order import');
    initItemSelectManual('#select-toko', '{{ route('toko.get-item') }}', 'pilih toko', '#global-modal');
    initItemSelectManual('.select-coa-kas', '{{ route('chart-account.get-item-keuangan') }}?kind=kas', 'Pilih Akun Kas',
        '#global-modal');
    var allTrans = [];

    function updateSelectToko(data) {
        // allToko = collect(data).map((itema) => {
        //     return collect(itema.details).pluck('toko').unique().all();
        // }).all()
        // fixToko = collect([]);
        // allToko.forEach(function(darray) {
        //     fixToko = fixToko.merge(darray);
        // });
        // fixToko = collect(fixToko).unique().all();
        // option = "";
        // option += `<option value="">All</option>`;

        // fixToko.forEach(function(item) {
        //     option += `<option value="${item}">${item}</option>`;
        // });
        // $('#select-search-toko').html(option);
    }

    var allCandidateFactur = [];
    var selectedFactur = [];
    var batchedCandidateFactur = [];
    var perPage = 15;
    var page = 1;

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
                // if (toko == "") {
                //     updateSelectToko(res.msg);
                // }
                batch = 0;
                count = 0;
                allCandidateFactur = {};
                selectedFactur = {};
                batchedCandidateFactur = {};
                batchedCandidateFactur[batch] = {};
                res.msg.forEach(function(item, i) {
                    count++;
                    if (count > perPage) {
                        batch++;
                        count = 1;
                        batchedCandidateFactur[batch] = {};
                    }
                    allCandidateFactur[item.id] = item;
                    batchedCandidateFactur[batch][item.id] = item;
                });
                renderTable(1);


            },
            error: function(res) {
                loading(0);

            }
        });
    }

    function renderTable(thepage = 1) {
        batch = thepage - 1;

        html = "";
        Object.keys(batchedCandidateFactur[batch]).forEach(function(key, i) {
            let item = allCandidateFactur[key];
            allTrans[item.id] = item;
            jumlah = item.details.length;
            $('#div-page').html(
                `   <div class="relative-pos">
                       <span class="absolute-pos" style="left:10px; top:5px;"> <i class="fas fa-file"></i> </span>
                    <input class="" style="width:100px; padding-left:30px;"
                      type="text" onchange="renderTable(this.value)" id="search-page" 
                      value="${thepage}" > of ${Object.keys(batchedCandidateFactur).length} Page
                    </div>
                `
            );
            allIDFactur = Object.keys(selectedFactur);
            console.log('all id factur', allIDFactur);
            item.details.forEach(function(detail, j) {
                html += `
                            <tr>
                                ${j==0?`
                                <td rowspan="${jumlah}">
                                    <input id="checkbox-tr${item.id}" ${ allIDFactur.includes(item.id.toString()) ? 'checked' : ''} type="checkbox" class="select-row-checkbox" data-id="${item.id}" onchange="selectThis('${item.id}')" />
                                </td>
                                <td rowspan="${jumlah}">${i+(batch*perPage)+1}</td>
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
                                    <button class="btn btn-success btn-sm" onclick="importData('${item.id}')">
                                        import NON
                                    </button>
                                    <button class="btn btn-success btn-sm mt-1" onclick="importData('${item.id}', 1)">
                                        import PPN
                                    </button>
                                </td>
                                `:''}
                            </tr>`;
            });


            $('#table-body').html(html);
        });
    }

    function updateTotalSelected() {
        // let countSelected = $('.select-row-checkbox:checked').length;
        // if (countSelected > 0) {
        //     $('#resume-selected').removeClass('hidden');
        // } else {
        //     $('#resume-selected').addClass('hidden');
        // }
        // let totalSelected = 0;
        // $('.select-row-checkbox:checked').each(function() {
        //     let id = $(this).data('id');
        //     let data = allTrans[id];
        //     totalSelected += collect(data.details).sum('total_price');
        // });
        // $('#total-selected').html(formatRupiah(totalSelected));
        let countSelected = Object.keys(selectedFactur).length;
        if (countSelected > 0) {
            $('#resume-selected').removeClass('hidden');
        } else {
            $('#resume-selected').addClass('hidden');
        }
        let totalSelected = 0;
        Object.keys(selectedFactur).forEach(function(id) {
            let data = selectedFactur[id];
            totalSelected += collect(data.details).sum('total_price');
        });
        $('#total-selected').html(formatRupiah(totalSelected));
    }

    function selectThis(id) {
        isChecked = $('#checkbox-tr' + id).is(':checked');
        if (!isChecked) {
            delete selectedFactur[id];
        } else {
            selectedFactur[id] = structuredClone(allCandidateFactur[id]);

        }
        updateTotalSelected();
    }


    function toggleSelectAll() {
        let isChecked = $('#select-all-checkbox').is(':checked');
        if (isChecked) {
            selectedFactur = structuredClone(allCandidateFactur);
        } else {
            selectedFactur = [];
        }
        $('.select-row-checkbox').prop('checked', isChecked);
        updateTotalSelected();
    }



    async function importAllChecked(isPPN = 0) {
        try {
            const ids = Object.keys(selectedFactur);
            const totalCount = ids.length;

            if (totalCount === 0) {
                swalInfo('opps', 'tidak ada data yang dipilih', 'info');
                return;
            }

            $('#div-progressbar').removeClass('hidden');
            $('#progress-import').css('width', '0%').html('0%');
            $('#btn-close-modal').prop('disabled', true);
            $('#btn-close-modal2').prop('disabled', true);

            let totalProgress = 0;

            for (const id of ids) {
                await importData(id, isPPN); // benar-benar nunggu 1 selesai dulu
                totalProgress++;

                const progressPercent = Math.round((totalProgress / totalCount) * 100);
                $('#progress-import').css('width', progressPercent + '%').html(progressPercent + '%');

                // kasih kesempatan browser repaint biar UI benar2 update
                await new Promise(r => requestAnimationFrame(r));
                // atau: await new Promise(r => setTimeout(r, 0));
            }

            $('#btn-close-modal').prop('disabled', false);
            $('#btn-close-modal2').prop('disabled', false);
            setTimeout(() => $('#div-progressbar').addClass('hidden'), 2000);
        } catch (err) {
            console.error('Error saat import:', err);
            notification('error', 'Terjadi kesalahan saat mengimpor data');
            $('#btn-close-modal').prop('disabled', false);
            $('#btn-close-modal2').prop('disabled', false);
            setTimeout(() => $('#div-progressbar').addClass('hidden'), 2000);
        }
    }

    function changeCustomImport() {
        let isChecked = $('#is-custom-import').is(':checked');
        if (isChecked) {
            $('#div-custom-import').removeClass('hidden');
        } else {
            $('#div-custom-import').addClass('hidden');
        }
    }



    var tokoParents = @json($toko_parents);

    function importData(id, isPPN = 0) {

        return new Promise((resolve, reject) => {
            let data = selectedFactur[id];
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
                    notification('error', `Toko ${data.toko_id} belum ada link ke parent toko`);
                    resolve({
                        status: 0,
                        msg: `Toko ${data.toko_id} belum ada link ke parent toko`
                    });
                    return;
                }


            }

            let dataPost = {
                created_at: date ? date : data.created_at,
                customer_name: data.customer_name,
                is_ppn: isPPN,
                sales_order_number: data.package_number,
                custom_stock_name: data.details.map(item => item.stock_name),
                reference_stock_id: data.details.map(item => item.stock_id),
                reference_stock_type: data.stock_type,
                quantity: data.details.map(item => item.quantity),
                qtyjadi: data.details.map(item => item.qtyjadi),
                price_unit: data.details.map(item => isPPN ? item.price / 1.11 : item.price),
                pricejadi: data.details.map(item => isPPN ? item.pricejadi / 1.11 : item.pricejadi),
                unit: data.details.map(item => item.unit),
                unitjadi: data.details.map(item => item.unitjadi),
                total_price: data.details.map(item => isPPN ? item.total_price / 1.11 : item
                    .total_price),
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
                    notification('error',
                        'Terjadi kesalahan saat mengimpor data'
                    ); // biar ketangkep di try/catch
                }
            });

        });
    }
</script>
