<x-app-layout>
    <div class="card shadow-sm mb-4">
        <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>KARTU HUTANG</strong> </h5>

        <div class="card-body">
            <div class="row mt-1">
                <div class="col-md-4">
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#basicModal"> 🔃 Mutasi</button>
                    <button type="button" class=" btn-primary" data-bs-toggle="modal" data-bs-target="#pelunasanModal"> 💵 Pelunasan</button>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
            </div>

            <div class="row">
               
                <div class="col-md-2">
                    <select name="bulan" id="month" class="form-select ">
                        <option value="">-- Bulan --</option>
                        @foreach(getListMonth() as $key => $month)
                        <option value="{{$key}}">{{$month}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tahun" id="year" class="form-select ">
                        <option value="">-- Tahun --</option>
                        @for($year=0; $year < 3; $year++)
                            <option value="{{intval(Date('Y')-$year)}}">{{intval(Date('Y')-$year)}}</option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button onclick="getSummary()" class="btn btn-primary btn-sm w-100">Cari</button>
                </div>
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
                    <h5 class="modal-title" id="exampleModalLabel1">Buat Mutasi Kartu Hutang</h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Invoice</label>
                            <input type="text" id="factur" class="form-control" placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Nilai mutasi</label>
                            <input type="text" id="amount_mutasi" autocomplete="off" class="form-control currency-input" placeholder="Nilai Mutasi" />
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPerson()" type="text" id="person_type" class="form-control" placeholder="type person">
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
                    <button id="btn-store" onclick="storeMutasi()" type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pelunasanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">Buat Pelunasan Kartu Hutang</h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">Nomer Invoice</label>
                            <input type="text" id="pelunasan-factur" class="form-control" placeholder="Nomer Invoice" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Nilai Pelunasan</label>
                            <input type="text" id="pelunasan-amount" autocomplete="off" class="form-control currency-input" placeholder="Nilai Pelunasan" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="amount_mutasi" class="form-label">Akun Pembayaran</label>
                            <select type="text" id="pelunasan-akun-bayar" class="form-control select-coa" >

                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col mb-0">
                            <label for="person_type" class="form-label">Type Person</label>
                            <select onchange="initSelectPersonPelunasan()" type="text" id="pelunasan-person_type" class="form-control" placeholder="type person">
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
                    <button id="btn-store-pelunasan" onclick="storePelunasan()" type="button" class="btn btn-primary">Save changes</button>
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
            $.ajax({
                url: '{{url("admin/kartu/kartu-hutang/create-mutation")}}',
                method: 'post',
                data: {
                    factur_supplier_number: $('#factur').val(),
                    amount_mutasi: formatDB($('#amount_mutasi').val(), 'id'),
                    person_id: $('#person_id option:selected').val(),
                    person_type: $('#person_type option:selected').val(),
                    _token: '{{csrf_token()}}'
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

        function storePelunasan() {
            $('#btn-store-pelunasan').attr('disabled', true);
            $('#btn-store-pelunasan').html(`<div class="spinner-border spinner-border-sm text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div> storing data..`);
            $.ajax({
                url: '{{url("admin/kartu/kartu-hutang/create-pelunasan")}}',
                method: 'post',
                data: {
                    factur_supplier_number: $('#pelunasan-factur').val(),
                    amount_bayar: formatDB($('#pelunasan-amount').val(), 'id'),
                    person_id: $('#pelunasan-person_id option:selected').val(),
                    account_bayar: $('#pelunasan-akun-bayar option:selected').val(),
                    person_type: $('#pelunasan-person_type option:selected').val(),
                    _token: '{{csrf_token()}}'
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
                initItemSelectManual('#person_id', '{{route("supplier.get-item")}}', 'Person Name ..', '#basicModal');
            }

            else{
                initItemSelectManual('#person_id', '{{route("other-person.get-item")}}', 'Person Name ..', '#basicModal');
       
            }
        }
        function initSelectPersonPelunasan() {
            type = $('#person_type option:selected').val();
            if (type === 'App\\Models\\Supplier') {
                console.log('init oy ' + type);
                initItemSelectManual('#pelunasan-person_id', '{{route("supplier.get-item")}}', 'Person Name ..', '#pelunasanModal');
            }
            else{
                initItemSelectManual('#pelunasan-person_id', '{{route("other-person.get-item")}}', 'Person Name ..', '#pelunasanModal');
       
            }
            
        }
        initSelectPerson();
        initSelectPersonPelunasan();
        initItemSelectManual('#pelunasan-akun-bayar','{{route("chart-account.get-item")}}','akun pembayaran ..','#pelunasanModal');
        

        function getSummary() {
            month= $('#month option:selected').val()??"";
            year= $('#year option:selected').val()??"";
            console.log(month+','+year);
            $.ajax({
                url: '{{route("kartu-hutang.get-summary")}}?month='+month+'&year='+year,
                method: 'get',
                success: function(res) {
                    console.log(res);
                    if (res.status == 1) {
                        html = "";
                        saldoAkhir=0;
                        res.msg.forEach(function(data,i) {
                            saldoAkhir+=parseInt(data.saldo);
                            html+= `
                                <tr>
                                    <td>${i+1}</td>
                                    <td>${data.person_name}</td>
                                    <td>${data.invoice_date}</td>
                                    <td>${data.factur_supplier_number}</td>
                                    <td class="textright">${formatRupiah(data.saldo_awal)}</td>
                                    <td class="textright">${formatRupiah(data.mutasi)}</td>
                                    <td class="textright">${formatRupiah(data.pelunasan)}</td>
                                    <td class="textright">${formatRupiah(data.saldo)}</td>
                                    <td class="textright">${formatRupiah(saldoAkhir)}</td>                              
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

        setTimeout(getSummary,100);
    </script>
    @endpush
</x-app-layout>