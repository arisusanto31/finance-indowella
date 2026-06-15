<x-app-layout>

    <div class="mb-4 card shadow p-3">
        <div class="text-primary-dark "> 🤖 <strong>BOT FIX JOURNAL </strong> </div>

        <div class="row mt-2 p-3 bglevel1">
            <div class="col-xs-12 bglevel1">
                <div class="row">
                    <div class="col-xs-6 col-md-2">
                        <input type="datetime-local" class="form-control" id="indexdate" placeholder="indexdate" />
                    </div>
                    <div class="col-xs-6 col-md-2">
                        <button onclick="startSearch()">start on</button>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12 mt-10" id="container-output">

                    </div>
                </div>
            </div>
        </div>

        <div class="text-primary-dark mt-3 "> 🤖 <strong>BOT FIX KARTU </strong> </div>
        <div class="row mt-2 p-3 bglevel1">
            <div class="col-xs-12 bglevel1">
                <div class="row">
                    <div class="col-xs-6 col-md-2">
                        <input type="datetime-local" class="form-control" id="indexdate-kartu" placeholder="indexdate" />
                    </div>
                    <div class="col-xs-6 col-md-2">
                        <select class="form-control" id="model">
                            <option value="KartuBDP">Kartu BDP</option>
                            <option value="KartuStock"> Kartu Stock</option>
                            <option value="KartuBahanJadi">Kartu bahan Jadi</option>
                            <option value="KartuHutang">Kartu Hutang</option>
                            <option value="KartuPiutang"> Kartu Piutang</option>
                            <option value="KartuDPSales">Kartu DP Sales</option>
                            <option value="KartuInventory">Kartu Inventory</option>
                            <option value="KartuPrepaidExpense">Kartu Prepaid Expense</option>
                        </select>
                    </div>
                    <div class="col-xs-6 col-md-2">
                        <button onclick="startSearchKartu()">start on</button>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12 mt-10" id="container-output-kartu">
                    </div>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script>
        var allNextIndex = [];
        var originIndex = null;



        function startSearchKartu() {
            model = $('#model').val();

            indexdate = $('#indexdate-kartu').val();
            $.ajax({
                url: '{{url("admin/cari-problem-kartu")}}?index_date=' + indexdate + '&model=' + model,
                method: 'get',
                success: async function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        if (model == 'KartuStock' || model == 'KartuBDP' || model == 'KartuBahanJadi') {
                            html = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>index date</th>
                                        <th>Stock </th>
                                        <th>production number</th>
                                        <th>qty mutasi</th>
                                        <th>rupiah mutasi</th>
                                        <th>qty saldo</th>
                                        <th>rupiah saldo</th>
                                        <th>qty saldo seharusnya </th>
                                        <th>rupiah saldo seharusnya</th>
                                        <th>action</th>
                                    </tr>
                                </thead>
                                <tbody id="problem-kartu-body">
                                </tbody>
                            </table>
                        `;
                        } else {
                            html = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>index date</th>
                                        <th>Number</th>
                                        <th>rupiah mutasi</th>
                                        <th>rupiah saldo</th>
                                        <th>rupiah saldo seharusnya</th>
                                        <th>action</th>
                                    </tr>
                                </thead>
                                <tbody id="problem-kartu-body">
                                </tbody>
                            </table>
                        `;
                        }
                        $('#container-output-kartu').html(html);
                        html = "";
                        res.msg.forEach(function(data) {
                            if (model == 'KartuStock' || model == 'KartuBDP' || model == 'KartuBahanJadi') {
                                html += `
                                <tr>
                                    <td>${data.id}</td>
                                    <td>${data.index_date}</td>
                                    <td>${res.stocks[data.stock_id]} [${data.stock_id}]</td>
                                    <td>${data.production_number}</td>
                                    <td>${data.mutasi_qty_backend != null ? `${data.mutasi_qty_backend} ${data.unit_backend}` : ''}</td>
                                    <td>${formatRupiah(data.mutasi_rupiah_total)}</td>
                                    <td> ${data.saldo_qty_backend ? `${formatRupiah(data.saldo_qty_backend)} ${data.unit_backend}` : ''}</td>
                                    <td>${formatRupiah(data.saldo_rupiah_total)}</td>
                                    <td>${data.qty_ok ? `${formatRupiah(data.qty_ok)} ${data.unit_backend}` : ''}</td>
                                    <td>${data.rupiah_ok ? formatRupiah(data.rupiah_ok) : ''}</td>
                                    <td id="status-kartu-${data.id}">
                                        
                                    </td>
                                </tr>
                            `;
                            } else {
                                html += `
                                <tr>
                                    <td>${data.id}</td>
                                    <td>${data.index_date}</td>
                                    <td>${data.number}</td>
                                    <td>${formatRupiah(data.mutasi_rupiah_total)}</td>
                                    <td> ${formatRupiah(data.saldo_rupiah_total)}</td>
                                    <td>${formatRupiah(data.rupiah_ok)}</td>
                                    <td id="status-kartu-${data.id}">
                                        
                                    </td>
                                </tr>
                            `;

                            }
                        });
                        $('#problem-kartu-body').html(html);

                        for (i = 0; i < res.msg.length; i++) {
                            data = res.msg[i];
                            await fixProblemKartu(data.id, model);

                        }

                    } else {
                        Swal.fire('success', 'sudah tidak ada problem', 'success');
                    }
                },
                error: function(res) {
                    Swal.fire('opps', 'something error', 'error');
                }
            });
        }

        async function startSearch() {
            loading(1);
            allNextIndex = [];
            originIndex = $('#indexdate').val();
            res = await $.ajax({
                url: '{{ url("admin/cari-problem-journal2") }}?date=' + $('#indexdate').val(),
                method: 'get',
            });

            console.log('start sarch', res);
            loading(0);
            console.log(res);
            if (res.status == 1) {
             
                html = "";
                html += `
                            <div class="mb-2 text-sm font-bold">index date : ${res.index_date}</div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>code group</th>
                                        <th>index date</th>
                                        <th>description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="problem-journal-body">
                               
                        `;
                res.msg.forEach(function(journal) {
                    html += `
                                <tr>
                                    <td>${journal.code_group}</td>
                                    <td>${journal.index_date}</td>
                                    <td>${journal.description}</td>
                                    <td id="status${journal.id}"></td>
                                </tr>
                            `;
                });
                html += `
                                </tbody>
                            </table>
                        `;
                $('#container-output').html(html);
                keys = Object.keys(res.msg);
                console.log('proses ',keys);
                for (const i of keys) {
                    console.log('recalculate iterasi - journal id  ',i, res.msg[i].id);
                    journal = res.msg[i];

                    $('#status' + journal.id).html('<i class="fas fa-spinner fa-spin"></i> recalculating');
                    d = await recalculate(journal.id);
                    if (d.status == 1) {
                        $('#status' + journal.id).html('<i class="fas fa-check colorgreen"></i> beres');
                    } else {
                        $('#status' + journal.id).html('<i class="fas fa-close colorred"></i> ajur');
                        swal('oppss', 'something error');
                    }
                }

            } else {

            }
        }

        function recalculate(id) {

            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: '{{ url("admin/recalculate-journal") }}/' + id,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                         
                           
                            resolve(res);
                        } else {
                          
                            reject(res);
                        }
                    },
                    error: function(res) {

                        reject(res);
                    }
                });
            });

        }

        function fixProblemKartu(id, model) {
            $('#status-kartu-' + id).html('<i class="fas fa-spinner fa-spin"></i> fixing');
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: '{{ url("admin/fix-problem-kartu") }}',
                    method: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        model: model
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            $('#status-kartu-' + id).html('<i class="fas fa-check colorgreen"></i> beres');
                            resolve(res);
                        } else {
                            $('#status-kartu-' + id).html('<i class="fas fa-close colorred"></i> ajur');
                            Swal.fire('oppss', 'something error', 'error');
                            reject(res);
                        }
                    },
                    error: function(res) {
                        $('#status-kartu-' + id).html('<i class="fas fa-close colorred"></i> ajur');
                        Swal.fire('opps', 'something error', 'error');
                        reject(res);
                    }
                });
            });
        }
    </script>
    @endpush
</x-app-layout>