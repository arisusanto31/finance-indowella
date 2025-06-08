<x-app-layout>
    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>KARTU DP SALE</strong>
            <div class="d-flex justify-content pe-4 mt-2 mb-3">
                <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                    << </button>
                        <span class="badge bg-primary d-flex justify-content-center align-items-center">
                            {{ getListMonth()[$month] }} {{ $year }}</span>
                        <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()">
                            >></button>

            </div>
        </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-4">
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#basicModal"> 🔃
                        Mutasi</button>
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#pelunasanModal">
                        💵 Pelunasan</button>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>




            <div class="table-responsive mt-2">
                <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>🦸 Customer</th>
                            <th>📅 Tanggal</th>
                            <th>#️⃣ No Sales order</th>
                            <th>🔖 Saldo Awal</th>
                            <th>🔃 Mutasi</th>
                            <th>💵 Pelunasan</th>
                            <th>💲 saldo</th>
                            <th>💸 saldo Akumulasi</th>
                            <th>📝 Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="body-kartu-hutang">
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="flex-column align-items-start">
                        <h5 class="modal-title" id="exampleModalLabel1">Buat Kartu DP Sale</h5>

                        <div class="form-check form-switch ">
                            <input class="form-check-input" type="checkbox" id="is_otomatis_jurnal" checked />
                            <label class="form-check-label" for="is_otomatis_jurnal">Buat Jurnal</label>
                        </div>

                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Tanggal</label>
                            <input type="datetime-local" id="date" class="form-control" placeholder="Tanggal" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Sales Order</label>
                            <input type="text" id="factur" class="form-control" placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Keterangan</label>
                            <input type="text" id="description" class="form-control" placeholder="keterangan" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Akun Uang Muka</label>
                            <select type="text" id="akun" class="form-control">
                                <option value="214000">Uang Muka Penjualan</option>
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Lawan Akun</label>
                            <select type="text" id="akun-lawan" class="form-control select-coa">

                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Nilai mutasi</label>
                            <input type="text" id="amount_mutasi" autocomplete="off"
                                class="form-control currency-input" placeholder="Nilai Mutasi" />
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPerson()" type="text" id="person_type"
                                class="form-control" placeholder="type person">
                                <option value="App\Models\Customer" selected> Customer</option>
                                <option value="App\Models\OtherPerson"> Orang Lain</option>
                            </select>
                        </div>
                        <div class="col mb-0">
                            <label for="dobBasic" class="form-label">Person</label>
                            <select class="form-control" id="person_id" placeholder="person"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button id="btn-store" onclick="storeMutasi()" type="button" class="btn btn-primary">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pelunasanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="flex-column align-items-start">
                        <h5 class="modal-title" id="exampleModalLabel1">Buat Pelunasan Kartu DP Sale</h5>

                        <div class="form-check form-switch ">
                            <input class="form-check-input" type="checkbox" id="is_pelunasan_otomatis_jurnal"
                                checked />
                            <label class="form-check-label" for="is_pelunasan_otomatis_jurnal">Buat Jurnal</label>
                        </div>

                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Tanggal</label>
                            <input type="datetime-local" id="pelunasan-date" class="form-control"
                                placeholder="Tanggal" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Sales Order</label>
                            <input type="text" id="pelunasan-factur" class="form-control"
                                placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Invoice</label>
                            <input type="text" id="pelunasan-invoice_number" class="form-control"
                                placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Keterangan</label>
                            <input type="text" id="pelunasan-description" class="form-control"
                                placeholder="keterangan" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Nilai Pelunasan</label>
                            <input type="text" id="pelunasan-amount" autocomplete="off"
                                class="form-control currency-input" placeholder="Nilai Pelunasan" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Akun Uang Muka</label>
                            <select type="text" id="pelunasan-akun" class="form-control select-coa">
                                <option value="214000">Uang Muka Penjualan</option>
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Lawan Akun</label>
                            <select type="text" id="pelunasan-akun-lawan" class="form-control select-coa">

                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPersonPelunasan()" type="text" id="pelunasan-person_type"
                                class="form-control" placeholder="type person">
                                <option value="App\Models\Customer" selected> Customer</option>
                                <option value="App\Models\OtherPerson"> Orang Lain</option>
                            </select>
                        </div>
                        <div class="col mb-0">
                            <label for="dobBasic" class="form-label">Person</label>
                            <select class="form-control" id="pelunasan-person_id" placeholder="person"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button id="btn-store-pelunasan" onclick="storePelunasan()" type="button"
                        class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function storeMutasi() {
                $('#btn-store').attr('disabled', true);
                $('#btn-store').html(`<div class="spinner-border spinner-border-sm text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div> storing data..`);
                data = {
                    date: $('#date').val(),
                    sales_order_number: $('#factur').val(),
                    amount_mutasi: formatDB($('#amount_mutasi').val(), 'id'),
                    person_id: $('#person_id option:selected').val(),
                    person_type: $('#person_type option:selected').val(),
                    code_group: $('#akun option:selected').val(),
                    description: $('#description').val(),
                    lawan_code_group: $('#akun-lawan option:selected').val(),
                    is_otomatis_jurnal: $('#is_otomatis_jurnal').is(':checked') ? 1 : 0,
                    _token: '{{ csrf_token() }}'
                };
                console.log(data);
                $.ajax({
                    url: '{{ url('admin/kartu/kartu-dp-sales/create-mutation') }}',
                    method: 'post',
                    data: data,
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('success', 'data berhasil tersubmit', 'success');
                            $('#btn-store').html('save changes');
                            $('#btn-store').attr('disabled', false);
                            $('#basicModal').modal('hide');
                        } else {
                            Swal.fire('opss', 'something error ' + res.msg, 'error');
                            $('#btn-store').html('save changes');
                            $('#btn-store').attr('disabled', false);
                        }
                    },
                    error: function(res) {
                        Swal.fire('opss', 'something error ', 'error');
                        $('#btn-store').html('save changes');
                        $('#btn-store').attr('disabled', false);
                    }
                })

            }

            function storePelunasan() {
                $('#btn-store-pelunasan').attr('disabled', true);
                $('#btn-store-pelunasan').html(`<div class="spinner-border spinner-border-sm text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div> storing data..`);
                $.ajax({
                    url: '{{ url('admin/kartu/kartu-dp-sales/create-pelunasan') }}',
                    method: 'post',
                    data: {
                        date: $('#pelunasan-date').val(),
                        sales_order_number: $('#pelunasan-factur').val(),
                        invoice_pack_number: $('#pelunasan-invoice_number').val(),
                        amount_bayar: formatDB($('#pelunasan-amount').val(), 'id'),
                        person_id: $('#pelunasan-person_id option:selected').val(),
                        account_bayar: $('#pelunasan-akun-bayar option:selected').val(),
                        person_type: $('#pelunasan-person_type option:selected').val(),
                        description: $('#pelunasan-description').val(),
                        lawan_code_group: $('#pelunasan-akun-lawan option:selected').val(),
                        code_group: $('#pelunasan-akun option:selected').val(),
                        is_otomatis_jurnal: $('#is_pelunasan_otomatis_jurnal').is(':checked') ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            Swal.fire('success', 'data berhasil tersubmit', 'success');
                            $('#btn-store-pelunasan').html('save changes');
                            $('#btn-store-pelunasan').attr('disabled', false);
                            $('#pelunasanModal').modal('hide');
                        } else {
                            Swal.fire('opss', 'something error ' + res.msg, 'error');
                            $('#btn-store-pelunasan').html('save changes');
                            $('#btn-store-pelunasan').attr('disabled', false);
                        }
                    },
                    error: function(res) {
                        Swal.fire('opss', 'something error ', 'error');
                        $('#btn-store-pelunasan').html('save changes');
                        $('#btn-store-pelunasan').attr('disabled', false);
                    }
                })

            }

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/kartu/kartu-dp-sales/main') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/kartu/kartu-dp-sales/main') }}?month=' + month + '&year=' + year;
            }

            function initSelectPerson() {
                type = $('#person_type option:selected').val();
                if (type === 'App\\Models\\Customer') {
                    console.log('init oy ' + type);
                    initItemSelectManual('#person_id', '{{ route('customer.get-item') }}', 'Person Name ..', '#basicModal');
                } else {
                    initItemSelectManual('#person_id', '{{ route('other-person.get-item') }}', 'Person Name ..',
                        '#basicModal');

                }
            }

            function initSelectPersonPelunasan() {
                type = $('#pelunasan-person_type option:selected').val();
                if (type === 'App\\Models\\Customer') {
                    console.log('init oy ' + type);
                    initItemSelectManual('#pelunasan-person_id', '{{ route('customer.get-item') }}', 'Person Name ..',
                        '#pelunasanModal');
                } else {
                    initItemSelectManual('#pelunasan-person_id', '{{ route('other-person.get-item') }}', 'Person Name ..',
                        '#pelunasanModal');

                }
            }
            initSelectPerson();
            initSelectPersonPelunasan();
            initItemSelectManual('#pelunasan-akun-lawan', '{{ route('chart-account.get-item') }}', 'akun lawan ..',
                '#pelunasanModal');
            initItemSelectManual('#pelunasan-akun-piutang', '{{ route('chart-account.get-item-keuangan') }}?kind=piutang',
                'akun piutang ..', '#pelunasanModal');
            initItemSelectManual('#akun-piutang', '{{ route('chart-account.get-item-keuangan') }}?kind=piutang',
                'akun piutang ..', '#basicModal');
            initItemSelectManual('#akun-lawan', '{{ route('chart-account.get-item') }}', 'akun lawan ..', '#basicModal');


            function getSummary() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                console.log(month + ',' + year);
                $.ajax({
                    url: '{{ route('kartu-dp-sales.get-summary') }}?month=' + month + '&year=' + year,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            html = "";
                            saldoAkhir = 0;
                            res.msg.forEach(function(data, i) {
                                saldoAkhir = (data.saldo);
                                html += `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.person_name}</td>
                                    <td>${data.invoice_date}</td>
                                    <td>${data.sales_order_number}</td>
                                    <td class="textright">${formatRupiah(data.saldo_awal)}</td>
                                    <td class="textright">${formatRupiah(data.mutasi)}</td>
                                    <td class="textright">${formatRupiah(data.pelunasan)}</td>
                                    <td class="textright">${formatRupiah(data.saldo)}</td>
                                    <td class="textright">${formatRupiah(saldoAkhir)}</td>      
                                      <td>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="showDetailKartu('${data.sales_order_number}')"><i class="fas fa-eye"></i> detail</button>
                                    </td>                          
                                </tr>
                            `;
                            });
                            $('#body-kartu-hutang').html(html);
                        } else {

                        }
                    },
                    error: function(res) {
                        console.log(res);
                    }
                });
            }

            function showDetailKartu(factur) {
                showDetailOnModal('{{ url('admin/kartu/kartu-dp-sales/show-detail') }}/' + factur, 'xl');

            }

            function refreshKartuDP(id) {
                $.ajax({
                    url: '{{ url('admin/kartu/kartu-dp-sales/refresh') }}/' + id,
                    method: 'get',
                    success: function(res) {
                        if (res.status == 1) {
                            swalInfo('success', 'berhasil refresh', 'success');
                        } else {
                            swalInfo('error', 'gagal refresh', 'error');
                        }
                    },
                    error: function(res) {
                        swalInfo('error', 'something error', 'error');
                    }
                });
            }
            setTimeout(getSummary, 100);
        </script>
    @endpush
</x-app-layout>
