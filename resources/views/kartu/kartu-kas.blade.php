<x-app-layout>

    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>KARTU KAS</strong>
            <div class="d-flex justify-content mt-1 pe-4 mb-3">
                <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                    << </button>
                        <span class="badge bg-primary d-flex justify-content-center align-items-center">
                            {{ getListMonth()[$month] }} {{ $year }}</span>
                        <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()">
                            >></button>

            </div>
        </h5>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>
            <div class="row">
                <div class="col-md-2">
                    <select onchange="searchBuku()" id="select-kind-kas" class="form-select">
                        @foreach ($kind_kas as $code => $item)
                            <option value="{{ $code }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12" id="container-kas">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            initItemSelectManual('.select-coa', '{{ route('chart-account.get-item-keuangan') }}?kind=kas', 'chart account');
            setTimeout(() => {
                searchBuku();
            }, 200);

            function searchBuku() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                kindKas = $('#select-kind-kas option:selected').val();
                $.ajax({
                    url: '{{ route('kartu-kas.get-buku-kas') }}?kind=' + kindKas + '&month=' + month + '&year=' + year,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
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
                                            <th>#️⃣ No Jurnal</th>
                                            <th>🔢 LAWAN COA</th>
                                            <th>📎 Description</th>
                                            <th>🔃 Debet</th>
                                            <th>🔃 Kredit</th>
                                            <th>💲saldo</th>
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
                                        
                                    </tr>
                                    `;
                                data = res.msg[codeKas];
                                if (data.length == 0) {
                                    html += `
                            <tr>
                                <td colspan="8" class="text-center">🤷‍♂️ Tidak ada data</td>
                            </tr>
                            `;
                                }
                                lastSaldo = res.saldo_awal[codeKas];

                                data.forEach((item, index) => {
                                    tanggal = formatNormalDateTime(new Date(item.created_at));
                                    lastSaldo = parseFloat(item.amount_saldo);
                                    html += `
                                    <tr>
                                        <td>${index+1}</td>
                                        <td>${tanggal}</td>
                                        <td>${item.journal_number} </td>
                                        <td>${item.lawan_code_group} - ${item.lawan_code.name}</td>
                                        <td>${item.description}</td>
                                        <td>${formatRupiah(item.amount_debet)}</td>
                                        <td>${formatRupiah(item.amount_kredit)}</td>
                                        <td>${formatRupiah(item.amount_saldo)}</td>
                                    </tr>
                                    `;
                                });
                                html += `

                                    <tr>
                                        <td>+</td>
                                        <td><input type="datetime-local" id="date-${codeKas}" class="form-control" /></td>
                                        <td> </td>
                                        <td><select class="form-control select-coa-table" id="coa-${codeKas}"> </select></td>
                                        <td><input type="text" class="form-control" id="description-${codeKas}" placeholder="description"/></td>
                                        <td><input type="text" class="form-control" id="amount-debet${codeKas}" placeholder="debet" /></td>
                                        <td><input type="text" class="form-control" id="amount-kredit${codeKas}" placeholder="kredit" /> </td>
                                         <td><button class="btn btn-primary" onclick="addKas('${codeKas}')" >submit </button></td>
                                    </tr>
                                  </tbody>
                                  <tfoot>
                                      <tr>
                                          <td colspan="5" class="text-end">Total</td>
                                          <td><strong>${formatRupiah(data.reduce((acc, item) => acc + parseFloat(item.amount_debet), 0))} </strong></td>
                                          <td><strong>${formatRupiah(data.reduce((acc, item) => acc + parseFloat(item.amount_kredit), 0))} </strong></td>
                                          <td><strong>${formatRupiah(lastSaldo)} </strong></td>
                                      </tr>
                                    </tfoot>
                                </table>
                            </div>
                            `;
                            });

                            $('#container-kas').html(html);
                            initItemSelectManual('.select-coa-table', '{{ route('chart-account.get-item') }}');

                        } else {
                            Swal.fire('opps', res.msg, 'error');
                        }
                    },
                    error: function(res) {
                        Swal.fire('opps', 'Gagal mendapatkan data', 'error');
                    }
                });
            }

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/kartu/kartu-kas/main') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/kartu/kartu-kas/main') }}?month=' + month + '&year=' + year;
            }

            function addKas(codeKas) {
                date = formatNormalDateTime(new Date($('#date-' + codeKas).val()));
                coa = $('#coa-' + codeKas + ' option:selected').val();
                description = $('#description-' + codeKas).val();
                amountDebet = $('#amount-debet' + codeKas).val();
                amountKredit = $('#amount-kredit' + codeKas).val();
                if (date == '' || coa == '' || description == '' || amountDebet == '' || amountKredit == '') {
                    Swal.fire('opps', 'Semua field harus diisi', 'error');
                    return;
                }
                data = {
                    date: date,
                    lawan_code_group: coa,
                    description: description,
                    amount_debet: amountDebet,
                    amount_kredit: amountKredit,
                    code_group: codeKas,
                    _token: '{{ csrf_token() }}'
                };
                console.log(data);
                swalConfirmAndSubmit({
                    url: '{{ route('kartu-kas.add-kas') }}',
                    data: data,
                    onSuccess: function(res) {
                        searchBuku();
                    }
                });

            }
        </script>
    @endpush

</x-app-layout>
