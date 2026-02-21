<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">Import purchase dari excel</h5>

    <button type="button" id="btn-close-modal" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
        aria-label="Close"></button>
</div>
<div class="modal-body">

    <div class="bglevel1 p-2 mb-2">
        <b>Import file </b> 
        <br>
        <button onclick="downloadTemplatePembelian()" class="btn btn-success"> <i
                class="fas fa-file-excel"></i> Download Template</button>
        <input type="file" id="import-file-input" class="form-control" />
        <button class="btn btn-primary mt-2" onclick="getImportData()">Load Data</button>
    </div>
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
            <div class="col-md-12 hidden" id="resume-selected">
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
            <pre id ="halo"></pre>
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
                            <th>Diskon </th>
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


    function downloadTemplatePembelian() {


        url = "{{ url('admin/invoice/download-template-pembelian') }}";
        var win = window.open(url, '_blank');


    }

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
        file = $('#import-file-input')[0].files[0];
        let formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('book_journal_id', id);
        $.ajax({
            url: '{{ route('invoice.purchase-get-data-import-excel') }}',
            data: formData,
            method: 'post',
            processData: false,
            contentType: false,
            success: function(res) {
                console.log(res);
                if (res.status == 0) {
                    swalInfo('opps', res.message, 'error');
                    loading(0);
                    return;
                }
                allTrans = collect(res.data).keyBy('id').all();
                console.log(allTrans);

                res.data.forEach(function(item, i) {
                    let detailsHtml = '';
                    jumlah = item.details.length;
                    item.details.forEach(function(detail, j) {
                        detailsHtml += `
                                <tr>
                                    ${j==0?`
                                    <td rowspan="${jumlah}">
                                        ${item.is_imported ? `<i class="fas fa-check color-primary"></i>` :`
                                        <input type="checkbox" class="select-row-checkbox" data-id="${item.id}" onchange="updateTotalSelected()" />`}
                                    </td>
                                    <td rowspan="${jumlah}">${i+1}</td>
                                    <td rowspan="${jumlah}">${formatNormalDate(new Date(item.created_at))}</td>
                                    <td rowspan="${jumlah}">${item.package_number} (${item.supplier})</td>
                                    <td rowspan="${jumlah}">${item.toko_name}</td>
                                    `:''}
                                    <td>${detail.stock_name}</td>
                                    <td>${detail['quantity']}</td>
                                    <td>${detail['unit']}</td>
                                    <td>${formatRupiah(detail['price'])}</td>
                                    <td>${formatRupiah(detail['discount'])}</td>
                                    <td>${formatRupiah(detail['total_price'])}</td>
                                    ${j==0?`
                                    <td rowspan="${jumlah}"> ${formatRupiah(collect(item.details).sum('total_price'))}</td>
                                    <td rowspan="${jumlah}" id="status${item.id}">
                                        <button class="btn btn-success btn-sm" onclick="importDataSingle('${item.id}')">
                                            import NON
                                        </button>
                                        <button class="btn btn-success btn-sm mt-1" onclick="importDataSingle('${item.id}', 1)">
                                            import PPN
                                        </button>
                                    </td>
                                    `:''}
                                </tr>`;
                    });
                    $('#table-body').append(detailsHtml);
                });
                loading(0);
            },
            error: function(err) {
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



    async function importAllChecked(isPPN = 0) {
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
            await importData(id, isPPN);
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





    var tokoParents = @json($toko_parents);

    function importDataSingle(id, isPPN = 0) {
        importData(id, isPPN).then(function(res) {
            console.log(res);
            if (res.status == 0) {
                swalInfo('opps', res.msg, 'error');
            }
        }).catch(function(err) {
            console.log(err);
            swalInfo('opps', 'terjadi kesalahan saat import data', 'error');
        });
    }

    function importData(id, isPPN = 0) {

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
                // if (tokoParents[data.toko_id] == undefined || tokoParents[data.toko_id] == null) {
                //     swalInfo('opps', `Toko ${data.toko_id} belum ada link ke parent toko`, 'info');
                //     return reject(new Error('Toko belum ada link ke parent toko'));
                // }
            }

            let dataPost = {
                date: date ? date : data.created_at,
                supplier_name: data.supplier,
                is_ppn: isPPN,
                fp_number: data.fp_number,
                surat_jalan: data.surat_jalan,
                invoice_pack_number: data.package_number,
                factur_supplier_number: data.package_number,
                reference_stock_id: data.details.map(item => item.stock_id),
                reference_stock_type: data.stock_type,
                quantity: data.details.map(item => item.quantity),
                qtyjadi: data.details.map(item => item.qtyjadi),
                price_unit: data.details.map(item => isPPN ? item.price / 1.11 : item.price),
                pricejadi: data.details.map(item => isPPN ? item.pricejadi / 1.11 : item.pricejadi),
                unit: data.details.map(item => item.unit),
                unitjadi: data.details.map(item => item.unitjadi),
                total_price: data.details.map(item => isPPN ? item.total_price / 1.11 : item.total_price),
                toko_id: data.toko_id,
                detail_reference_id: data.details.map(item => item.reference_id),
                detail_reference_type: data.details.map(item => item.reference_type),
                reference_id: data.id,
                reference_type: data.reference_type,
                _token: '{{ csrf_token() }}'
            };

            console.log(dataPost);
          
            $.ajax({
                url: '{{ route('invoice.purchase.store') }}',
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
