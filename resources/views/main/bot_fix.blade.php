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

        function recalculate(id) {
            $.ajax({
                url: '{{ url("admin/recalculate-journal") }}/' + id,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        $('#status' + id).html('<i class="fas fa-check colorgreen"></i> beres');
                        // $('#indexdate').val(res.journal.index_date);
                        allNextIndex.push(res.journal.index_date);
                        startSearch();
                    } else {
                        $('#status' + id).html('<i class="fas fa-close colorred"></i> ajur');

                        swal('oppss', 'something error');
                    }
                },
                error: function(res) {
                    swal('opps', 'something error');
                }
            });
        }

        function startSearchKartu() {
            model = $('#model').val();

            indexdate = $('#indexdate-kartu').val();
            $.ajax({
                url: '{{url("admin/cari-problem-kartu")}}?index_date=' + indexdate + '&model=' + model,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html ="";
                        html = `
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>id</th>
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
                        $('#container-output-kartu').html(html);
                        html="";
                        res.msg.forEach(function(data){
                            html+=`
                                <tr>
                                    <td>${data.id}</td>
                                    <td>${data.production_number}</td>
                                    <td>${data.mutasi_qty_backend} ${data.unit_backend}</td>
                                    <td>${formatRupiah(data.mutasi_rupiah_total)}</td>
                                    <td>${formatRupiah(data.saldo_qty_backend)} ${data.unit_backend}</td>
                                    <td>${formatRupiah(data.saldo_rupiah_total)}</td>
                                    <td>${formatRupiah(data.qty_ok)} ${data.unit_backend}</td>
                                    <td>${formatRupiah(data.rupiah_ok)}</td>
                                </tr>
                            `;
                        });
                        $('#problem-kartu-body').html(html);

                    } else {
                        Swal.fire('success', 'sudah tidak ada problem','success');
                    }
                },
                error: function(res) {
                    Swal.fire('opps', 'something error','error');
                }
            });
        }

        function startSearch() {
            loading(1);
            allNextIndex = [];
            originIndex = $('#indexdate').val();
            $.ajax({
                url: '{{ url("admin/cari-problem-journal") }}?index_date=' + $('#indexdate').val(),
                method: 'get',
                success: function(res) {
                    loading(0);
                    console.log(res);
                    if (res.status == 1) {
                        id = res.last.id;
                        html = "";
                        html += '<div class="row">';
                        html += '   <div class="col-xs-4">';
                        html += '        <p><i class="fas fa-circle" ></i> ' + res.last.journal_number +
                            ' code:' +
                            res.last.code_group + '</p>';
                        html += '        <p class="ml-10">' + res.last.index_date + ' [' + res.last.id + ']</p>';
                        html += '        <p class="ml-10">' + res.last.description + '</p>';

                        // html += '        <p class="ml-10">debet: ' + res.last.amount_debet + '</p>';
                        // html += '        <p class="ml-10">kredit: ' + res.last.amount_kredit + '</p>';
                        html += '        <p class="ml-10">saldo: ' + res.last.amount_saldo + '</p>';
                        html += '   </div>';
                        html += '   <div class="col-xs-4" >';
                        html += '        <p><i class="fas fa-circle" ></i> ' + res.now.journal_number +
                            ' code:' +
                            res.now.code_group + '</p>';
                        html += '        <p class="ml-10">' + res.now.index_date + '[' + res.now.id + ']</p>';
                        html += '        <p class="ml-10">' + res.now.description + '</p>';
                        html += '        <p class="ml-10">debet: ' + res.last.amount_debet + '</p>';
                        html += '        <p class="ml-10">kredit: ' + res.last.amount_kredit + '</p>';
                        html += '        <p class="ml-10">saldo: ' + res.last.amount_saldo + '</p>';
                        html += '   </div>';
                        html += '   <div class="col-xs-1">'
                        html += '        <p id="status' + res.now.id +
                            '"> <i class="fas fa-spinner fa-spin"></i> fixing </p>';
                        html += '   </div>';

                        html += '   <div class="clearfix"  style="border-bottom:1px solid black"></div>';
                        html += '</div>';
                        $('#container-output').append(html);
                        setTimeout(function() {
                            recalculate(res.now.id);
                        }, 100);

                    } else {
                        if (allNextIndex.length == 0) {
                            loading(0);
                            swalInfo('success', 'sudah tidak ada problem', 'success');
                        } else {
                            index = allNextIndex.shift();
                            $('#indexdate').val(index);
                            setTimeout(startSearch, 100);
                        }
                    }
                },
                error: function(res) {
                    loading(0);
                    swalInfo('opps', 'something error');
                }
            });
        }
    </script>

    @endpush
</x-app-layout>