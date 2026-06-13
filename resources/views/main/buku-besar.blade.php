<x-app-layout>

    <style>
        btn-big-custom {
            padding: 20px 40px;
            font-size: 1.5rem;
            border-radius: 8px;
        }
    </style>

    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 📚 <strong>BUKU BESAR </strong> </h5>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>
            <div class="row">
                <div class="col-md-3">
                    <select id="coa" class="form-select select-coa"></select>

                </div>
                <div class="col-md-2 " style="position:relative;">
                    <span style="position:absolute; left:19px; top:9px;">from:</span>
                    <input type="date" class="form-control" style="padding-left:60px;" id="dateawal" placeholder="Tanggal Awal">
                </div>
                <div class="col-md-2 " style="position:relative;">
                    <span style="position:absolute; left:19px; top:9px;">to:</span>
                    <input type="date" class="form-control" style="padding-left:60px;" id="dateakhir" placeholder="Tanggal Akhir">
                </div>
                <div class="col-md-2">
                    <button onclick="searchData()" id="btn-search" class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
            </div>


            <div class="row mt-2">
                <div class="col-md-12" id="container-buku">

                </div>
            </div>


        </div>
    </div>

    @push('scripts')
        <script>
            initItemSelectManual('.select-coa', '{{ route("chart-account.get-item-all") }}', 'chart account');

            function searchData() {
                startdate = $('#dateawal').val();
                enddate = $('#dateakhir').val();
                coa = $('#coa option:selected').val();
                $('#btn-search').attr('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
                $.ajax({
                    url: '{{ url("admin/jurnal/get-buku-besar") }}?coa=' + coa + '&dateawal=' + startdate + '&dateakhir=' + enddate,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        $('#btn-search').attr('disabled', false).html('Cari');
                        if (res.status == 1) {
                            html = '';
                            Object.keys(res.msg).forEach(function eachKas(codeKas) {
                                html += `
                            <p class="text-primary-dark fs-5 mt-3 mb-0 "> ${codeKas} - ${res.chart_accounts[codeKas]} </p>
                            <div class="table-responsive mt-1">
                                <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
                                    <thead class="bg-white text-dark text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>📅 Tanggal</th>
                                            <th>🔢 ID jurnal</th>
                                            <th>#️⃣ No Jurnal</th>
                                            <th>🔢 LAWAN COA</th>
                                            <th>📎 Description</th>
                                            <th>🔃 Debet</th>
                                            <th>🔃 Kredit</th>
                                            <th>💲saldo</th>
                                            <th>status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="body-mutasi-bukubesar">
                            `;

                                html += `
                                    <tr>
                                        <td colspan="5" class="text-center">Saldo Awal </td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td> ${formatRupiah(res.saldo_awal[codeKas])}</td>
                                        <td> </td>
                                    </tr>
                                    `;
                                data = res.msg[codeKas];
                                if (data.length == 0) {
                                    html += `
                            <tr>
                                <td colspan="9" class="text-center">🤷‍♂️ Tidak ada data</td>
                            </tr>
                            `;
                                }
                                lastSaldo = res.saldo_awal[codeKas];
                                flagNotValid = false;
                                data.forEach((item, index) => {
                                    tanggal = formatNormalDateTime(new Date(item.created_at));
                                    lastSaldo = parseFloat(item.amount_saldo);
                                    html += `
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${tanggal}</td>
                                        <td>${item.id} ${item.is_locked==1?`<span class="badge bg-warning"><i class="fa fa-lock"></i> </span>`:''}</td>
                                        <td>${item.journal_number} </td>
                                        <td>
                                            ${item.is_locked==1?`
                                                ${item.lawan_code_group} - ${res.chart_accounts[item.lawan_code_group]??''}
                                            `:`
                                            <select onchange="changeLawanCode(${item.id}, this.value)" class="select-lawan-code">
                                                <option value="${item.lawan_code_group}">${item.lawan_code_group} - ${res.chart_accounts[item.lawan_code_group]??''}</option>   
                                            </select>`}
                                        </td>
                                        <td>${item.description}</td>
                                        <td>${formatRupiah(item.amount_debet)}</td>
                                        <td>${formatRupiah(item.amount_kredit)}</td>
                                        <td>${formatRupiah(item.amount_saldo)}</td>
                                        <td>${item.verified_by==1?`<span class="badge bg-success"> valid </span>`:`<span class="badge bg-danger" >not valid</span>`}</td>
                                    </tr>
                                    `;
                                    if (item.verified_by != 1)
                                        flagNotValid = true;
                                });
                                html += `
                                  </tbody>
                                  <tfoot>
                                      <tr>
                                          <td colspan="6" class="text-end">Total</td>
                                          <td><strong>${formatRupiah(data.reduce((acc, item) => acc + parseFloat(item.amount_debet), 0))} </strong></td>
                                          <td><strong>${formatRupiah(data.reduce((acc, item) => acc + parseFloat(item.amount_kredit), 0))} </strong></td>
                                          <td><strong>${formatRupiah(lastSaldo)} </strong></td>
                                          <td> ${flagNotValid==true?`<button onclick="updateNotValid(${codeKas})"> <i class="fa fa-refresh"></i> not valid </button >`:''}</td>
                                      </tr>
                                    </tfoot>
                                </table>
                            </div>
                            `;
                            });

                            $('#container-buku').html(html);
                            initItemSelectManual('.select-lawan-code', '{{ route("chart-account.get-item-all") }}', 'chart account');
                        } else {
                            Swal.fire('opps', res.msg, 'error');
                        }
                    },
                    error: function(res) {
                        $('#btn-search').attr('disabled', false).html('Cari');
                        Swal.fire('opps', 'Gagal mendapatkan data', 'error');
                    }
                });
            }

            function changeLawanCode(id,lawanCode){
                loading(1);
                $.ajax({
                    url: '{{ url("admin/jurnal/change-lawan-code") }}',
                    method: 'post',
                    data:{
                        journal_id:id,
                        lawan_code:lawanCode,
                        _token:'{{ csrf_token() }}'
                    },
                    success: function(res) {
                        loading(0);
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('Berhasil', res.msg, 'success');
                            searchData();
                        } else {
                            Swal.fire('opps', res.msg, 'error');
                        }
                    },
                    error: function(res) {
                        loading(0);
                        Swal.fire('opps', 'Gagal mendapatkan data', 'error');
                    }
                });
            }

            function updateNotValid(codeGroup) {
                loading(1);
                $.ajax({
                    url: '{{ url("admin/jurnal/update-not-valid") }}?code_group=' + codeGroup,
                    method: 'get',
                    success: function(res) {
                        loading(0);
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('Berhasil', res.msg, 'success');
                            searchData();
                        } else {
                            Swal.fire('opps', res.msg, 'error');
                        }
                    },
                    error: function(res) {
                        loading(0);
                        Swal.fire('opps', 'Gagal mendapatkan data', 'error');
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
