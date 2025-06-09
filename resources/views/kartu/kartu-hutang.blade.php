<x-app-layout>
    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>KARTU HUTANG</strong>
            <div class="d-flex justify-content pe-4 mb-3">
                <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                    << </button>
                        <span class="badge bg-primary d-flex justify-content-center align-items-center">
                            {{ getListMonth()[$month] }} {{ $year }}</span>
                        <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="nextMonth()">
                            >>
                        </button>
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
                            <th>🦸 Supplier</th>
                            <th>📅 Tanggal</th>
                            <th>#️⃣ No Invoice</th>
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
                        <h5 class="modal-title" id="exampleModalLabel1">Buat Mutasi Kartu Hutang</h5>
                        <div class="form-check form-switch ">
                            <input class="form-check-input" type="checkbox" id="is_otomatis_jurnal" checked />
                            <label class="form-check-label" for="is_otomatis_jurnal">Buat Jurnal</label>
                        </div>
                    </div>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">Date</label>
                        <input type="datetime-local" id="mutasi-date" class="form-control" value="{{ now() }}"
                            placeholder="date" />
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Purchase Order</label>
                            <input type="text" id="purchase_order" class="form-control"
                                placeholder="Nomer Purchase Order" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Invoice</label>
                            <input type="text" id="factur" class="form-control" placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Deskripsi Jurnal</label>
                            <input type="text" id="description" class="form-control" placeholder="deskripsi" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Nilai mutasi</label>
                            <input type="text" id="amount_mutasi" autocomplete="off"
                                class="form-control currency-input" placeholder="Nilai Mutasi" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Akun hutang</label>
                            <select type="text" id="akun-hutang" class="form-control select-coa">
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Lawan Akun</label>
                            <select type="text" id="akun-lawan-hutang" class="form-control select-coa">
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPerson()" type="text" id="person_type"
                                class="form-control" placeholder="type person">
                                <option value="App\Models\Supplier" selected> Supplier</option>
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
                        <h5 class="modal-title" id="exampleModalLabel1">Buat Pelunasan Kartu Hutang</h5>
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
                            <label for="nameBasic" class="form-label">Date</label>
                            <input type="datetime-local" id="pelunasan-date" class="form-control"
                                value="{{ now() }}" placeholder="date" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Purchase Order</label>
                            <input type="text" id="pelunasan-purchase_order" class="form-control"
                                placeholder="Nomer Purchase Order" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Invoice</label>
                            <input type="text" id="pelunasan-factur" class="form-control"
                                placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Deskripsi Jurnal</label>
                            <input type="text" id="pelunasan-description" class="form-control"
                                placeholder="Deskripsi" />
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
                            <label for="amount_mutasi" class="form-label">Akun Hutang</label>
                            <select type="text" id="pelunasan-akun-hutang" class="form-control select-coa">
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Akun Pembayaran</label>
                            <select type="text" id="pelunasan-akun-bayar" class="form-control select-coa">
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPersonPelunasan()" type="text" id="pelunasan-person_type"
                                class="form-control" placeholder="type person">
                                <option value="App\Models\Supplier" selected> Supplier</option>
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

                if ($('#factur').val() == "") {
                    swalInfo('opps', 'nomer invoice harus ada', 'warning');
                }
                $.ajax({
                    url: '{{ url('admin/kartu/kartu-hutang/create-mutation') }}',
                    method: 'post',
                    data: {
                        date: $('#mutasi-date').val(),
                        invoice_pack_number: $('#factur').val(),
                        purchase_order_number: $('#purchase_order').val(),
                        description: $('#description').val(),
                        amount_mutasi: formatDB($('#amount_mutasi').val(), 'id'),
                        person_id: $('#person_id option:selected').val(),
                        code_group: $('#akun-hutang option:selected').val(),
                        lawan_code_group: $('#akun-lawan-hutang option:selected').val(),
                        person_type: $('#person_type option:selected').val(),
                        is_otomatis_jurnal: $('#is_otomatis_jurnal').is(':checked') ? 1 : 0,
                        _token: '{{ csrf_token() }}'
                    },
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

            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/kartu/kartu-hutang/main') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/kartu/kartu-hutang/main') }}?month=' + month + '&year=' + year;
            }

            function storePelunasan() {
                $('#btn-store-pelunasan').attr('disabled', true);
                $('#btn-store-pelunasan').html(`<div class="spinner-border spinner-border-sm text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div> storing data..`);
                if ($('#pelunasan-factur').val() == "") {
                    swalInfo('opps', 'nomer invoice harus ada', 'warning');
                }
                $.ajax({
                    url: '{{ url('admin/kartu/kartu-hutang/create-pelunasan') }}',
                    method: 'post',
                    data: {
                        date: $('#pelunasan-date').val(),
                        invoice_pack_number: $('#pelunasan-factur').val(),
                        purchase_order_number: $('#pelunasan-purchase_order').val(),
                        description: $('#pelunasan-description').val(),
                        amount_bayar: formatDB($('#pelunasan-amount').val(), 'id'),
                        person_id: $('#pelunasan-person_id option:selected').val(),
                        code_group: $('#pelunasan-akun-hutang option:selected').val(),
                        lawan_code_group: $('#pelunasan-akun-bayar option:selected').val(),
                        person_type: $('#pelunasan-person_type option:selected').val(),
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

            function initSelectPerson() {
                type = $('#person_type option:selected').val();
                if (type === 'App\\Models\\Supplier') {
                    console.log('init oy ' + type);
                    initItemSelectManual('#person_id', '{{ route('supplier.get-item') }}', 'Person Name ..', '#basicModal');
                } else {
                    initItemSelectManual('#person_id', '{{ route('other-person.get-item') }}', 'Person Name ..',
                        '#basicModal');
                }
            }

            function initSelectPersonPelunasan() {
                type = $('#pelunasan-person_type option:selected').val();
                if (type === 'App\\Models\\Supplier') {
                    console.log('init oy ' + type);
                    initItemSelectManual('#pelunasan-person_id', '{{ route('supplier.get-item') }}', 'Person Name ..',
                        '#pelunasanModal');
                } else {
                    initItemSelectManual('#pelunasan-person_id', '{{ route('other-person.get-item') }}', 'Person Name ..',
                        '#pelunasanModal');
                }
            }
            initSelectPerson();
            initSelectPersonPelunasan();
            initItemSelectManual('#pelunasan-akun-bayar', '{{ route('chart-account.get-item') }}', 'akun pembayaran ..',
                '#pelunasanModal');
            initItemSelectManual('#pelunasan-akun-hutang', '{{ route('chart-account.get-item-keuangan') }}?kind=hutang',
                'akun hutang ..', '#pelunasanModal');
            initItemSelectManual('#akun-hutang', '{{ route('chart-account.get-item-keuangan') }}?kind=hutang',
                'akun hutang ..', '#basicModal');
            initItemSelectManual('#akun-lawan-hutang', '{{ route('chart-account.get-item') }}', 'akun lawan hutang ..',
                '#basicModal');

            function getSummary() {
                month = $('#month option:selected').val() ?? "";
                year = $('#year option:selected').val() ?? "";
                console.log(month + ',' + year);
                $.ajax({
                    url: '{{ route('kartu-hutang.get-summary') }}?month=' + month + '&year=' + year,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            html = "";
                            saldoAkhir = 0;
                            totalSaldoAwal = 0;
                            totalMutasi = 0;
                            totalPelunasan = 0;
                            totalSaldo = 0;
                            res.msg.forEach(function(data, i) {
                                saldoAkhir += parseInt(data.saldo);
                                totalSaldoAwal += formatDB(formatRupiah(data.saldo_awal));
                                totalMutasi += formatDB(formatRupiah(data.mutasi));
                                totalPelunasan += formatDB(formatRupiah(data.pelunasan));
                                totalSaldo += (data.saldo);

                                html += `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.person_name}</td>
                                    <td>${data.invoice_date}</td>
                                    <td>${data.invoice_pack_number}</td>
                                    <td class="textright">${formatRupiah(data.saldo_awal)}</td>
                                    <td class="textright">${formatRupiah(data.mutasi)}</td>
                                    <td class="textright">${formatRupiah(data.pelunasan)}</td>
                                    <td class="textright">${formatRupiah(data.saldo)}</td>
                                    <td class="textright">${formatRupiah(saldoAkhir)}</td>    
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="showDetailKartuHutang('${data.invoice_pack_number}')"><i class="fas fa-eye"></i> Detail</button>
                                    </td>                          
                                </tr>
                            `;
                            });
                            $('#body-kartu-hutang').html(html);
                            htmlFooter = `
                            <tr>
                                <td colspan="4" class="text-center">Total</td>
                                <td class="textright"><strong>${formatRupiah(totalSaldoAwal)} </strong></td>
                                <td class="textright"><strong>${formatRupiah(totalMutasi)}</strong></td>
                                <td class="textright"><strong>${formatRupiah(totalPelunasan)}</strong></td>
                                <td class="textright"><strong>${formatRupiah(totalSaldo)}</strong></td>
                                <td class="textright"><strong>${formatRupiah(saldoAkhir)}</strong></td>
                                <td></td>
                            </tr>
                            `;
                            $('#footer-kartu-hutang').append(htmlFooter);
                        } else {

                        }
                    },
                    error: function(res) {
                        console.log(res);
                    }
                });
            }

            setTimeout(getSummary, 100);

            function showDetailKartuHutang(factur) {
                showDetailOnModal('{{ url('admin/kartu/kartu-hutang/show-detail') }}/' + factur, 'xl');
            }
        </script>
    @endpush
</x-app-layout>
