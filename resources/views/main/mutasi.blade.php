<x-app-layout>

    <div class="card mt-3 rounded-3 mb-3">
        <h5 class="text-primary-dark card-header"> 📥 <strong>IMPORT AWAL DATA </strong> </h5>
        <div class="card-body">
            <form action="{{ route('jurnal.get-import-saldo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date">
                    </div>
                    <div class="col-md-6">
                        <input type="file" class="form-control" name="file">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" type="submit">Import</button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-xs-12" id="container-task">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm rounded-3 ">
        <h5 class="text-primary-dark card-header">
            <a href="javascript:void(openCardCreate())">

                <div class="flex-column align-items-start">
                    ⚒️ <strong>BUAT MUTASI JURNAL</strong>
                    <i id="icon-create" class="bx bx-caret-down toggle-icon"></i>
                    <div class="form-check form-switch ">
                        <input onchange="changeBackdate()" class="form-check-input mt-2" type="checkbox"
                            id="is_backdate" />
                        <label class="form-check-label mt-2" for="is_backdate">backdate</label>
                    </div>
                </div>
            </a>

        </h5>
        <div id="card-create" class="tree-toggle">
            <input type="hidden" value="{{ Date('Y-m-d H:i:s') }}" id="date-mutasi" />

            <div class="card-body" style="padding-top: 0px;">

                <div id="div-backdate" class="d-backdate">
                    Tanggal Backdate
                    <input type="date" class="form-control" placeholder="tanggal" id="date-mutasi-journal" />
                </div>
                <hr class="d-backdate">
                <div>
                    Debit
                    <button type="button" class="btn btn-sm btn-primary-light ms-2" id="addDebit">+tambah</button>
                </div>
                <div id="div-debet" class="debet-wrapper">
                    <div id="debet1" class="row rowdebet g-2 mb-2 ">
                        <div class="col-md-4">
                            <select id="dcodegroup1" onchange="changeCode('debet','1')" class="form-control select-coa">
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input id="dnote1" type="text" class="form-control" placeholder="Note">
                        </div>
                        <div class="col-md-4">
                            <input id="damount1" type="text" class="form-control currency-input"
                                placeholder="Amount">
                        </div>
                    </div>
                </div>
                <hr>
                <div>
                    Kredit
                    <button type="button" class="btn btn-sm btn-primary-light ms-2" id="addKredit">+tambah</button>
                </div>
                <div id="div-kredit" class="kredit-wrapper">
                    <!-- Baris kredit pertama -->
                    <div id="kredit1" class="row  rowkredit g-2 mb-2 ">
                        <div class="col-md-4">
                            <select id="kcodegroup1" onchange="changeCode('kredit','1')"
                                class="form-control select-coa">

                            </select>
                        </div>
                        <div class="col-md-4">
                            <input id="knote1" type="text" class="form-control" placeholder="Note">
                        </div>
                        <div class="col-md-4">
                            <input id="kamount1" type="text" class="form-control currency-input"
                                placeholder="Amount">
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button onclick="submitJournalManual()" class="btn btn-primary w-100">Submit Journal</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3 rounded-3 ">
        <h5 class="text-primary-dark card-header">
            <a href="javascript:void(openCardClosing())">
                <div class="flex-column align-items-start">
                    ⚒️ <strong>BUAT JURNAL PENUTUP</strong>
                    <i id="icon-closing" class="bx bx-caret-down toggle-icon"></i>
                </div>
            </a>

        </h5>
        <div id="card-closing" class="tree-toggle">

            <div class="card-body" style="padding-top: 0px;">
                <div class="row">
                    <div class="col-md-2">
                        <input type="date" onchange="changeDateClosingJournal()" class="form-control"
                            placeholder="tanggal penutupan" id="date-closing-journal" />
                    </div>
                    <div class="col-md-3">
                        <label>keterangan: </label>
                        <div id="keterangan-closing"></div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" id="btn-closing"
                            onclick="submitClosingJournal()">Submit Jurnal
                            Penutup</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div>
            <h5 class="text-primary-dark card-header"> 📤 <strong>MUTASI JURNAL </strong>
                <div class="d-flex justify-content pe-4 mt-1 mb-3">
                    <button type="button" class="btn colorblack btn-primary-lightest px-2" onclick="prevMonth()">
                        << </button>
                            <span class="badge bg-primary d-flex justify-content-center align-items-center">
                                {{ getListMonth()[$month] }} {{ $year }}</span>
                            <button type="button" class="btn colorblack btn-primary-lightest px-2"
                                onclick="nextMonth()"> >></button>

                </div>
            </h5>

            <div class="pull-right" style="float:right; ">
                <div style="width:150px;position:relative; float:right;">
                    <button class="btn-control" type="text" id="btn-search" onclick="trySearch() ">
                        search</button>

                </div>
                <div style="width:150px;position:relative; float:right;">
                    <input class="form-control ps-4" type="text" placeholder="deskripsi" id="search-description">
                    <span style="position:absolute; left:10px; top:0px; transform: translate(0%, 50%);">
                        <i class="fas fa-search"></i> </span>
                </div>
                <div style="width:150px;position:relative; float:right;">
                    <input class="form-control ps-4" type="text" placeholder="nomer JURNAL"
                        id="search-journal_number">
                    <span style="position:absolute; left:10px; top:0px; transform: translate(0%, 50%);">
                        <i class="fas fa-search"></i> </span>
                </div>
                <div style="width:150px;position:relative; float:right;">
                    <input class="form-control ps-4" type="text" placeholder="nama COA" id="search-name_coa">
                    <span style="position:absolute; left:10px; top:0px; transform: translate(0%, 50%);">
                        <i class="fas fa-search"></i> </span>
                </div>
                <div style="width:150px;position:relative; float:right;">
                    <input class="form-control ps-4" type="text" placeholder="kode COA" value=""
                        id="search-coa">
                    <span style="position:absolute; left:10px; top:0px; transform: translate(0%, 50%);">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <div style="width:100px;" class="position-relative pull-right">
                    <div style="position:relative; float:right;">
                        <span id="max-page" class="position-absolute" style="right:5px; top:5px;">of </span>
                        <div class="">
                            <input class="form-control ps-4 pr-10" type="text" placeholder="halaman"
                                value="1" onchange="halamanChange()" id="halaman-input">
                            <span style="position:absolute; left:5px; top:0px; transform: translate(0%, 50%);">
                                <i class="fas fa-file"></i>
                        </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>📅 Tanggal</th>
                            <th>#️⃣ No Jurnal</th>
                            <th>🔢 COA</th>
                            <th>📎 Description</th>
                            <th>📥 Debet</th>
                            <th>📤 Kredit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="body-mutasi-jurnal">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var iRowDebet = 1;
            var iRowKredit = 1;


            setTimeout(() => {
                initItemSelectManual('.select-coa', '{{ url('admin/master/chart-account/get-item') }}',
                    'chart account');

            }, 100);


            function changeDateClosingJournal() {
                date = new Date($('#date-closing-journal').val());
                console.log(date);
                const pad = (n) => n.toString().padStart(2, '0');
                const year = date.getFullYear();
                const month = pad(date.getMonth() + 1); // bulan dimulai dari 0
                const day = '01';
                date = (`${year}-${month}-${day}`);
                console.log(date);
                $('#date-closing-journal').val(date);

                $.ajax({
                    url: '{{ url('admin/jurnal/get-closing-journal') }}?date=' + date,
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            if (res.msg == null) {
                                $('#keterangan-closing').html('Tidak ada jurnal penutup di bulan ini');
                                $('#btn-closing').prop('disabled', false);
                            } else {
                                $('#keterangan-closing').html('sudah ada jurnal penutup  bulan ini di ' + res.msg);
                                $('#btn-closing').prop('disabled', true);
                            }
                        } else {

                        }
                    },
                    error: function(err) {

                    }
                });
            }

            function submitClosingJournal() {
                swalConfirmAndSubmit({
                    url: '{{ url('admin/jurnal/tutup-jurnal') }}',
                    data: {
                        monthyear: $('#date-closing-journal').val(),
                        aksi:1,
                        _token: '{{ csrf_token() }}'
                    },
                    onSuccess: (res) => {
                        if (res.status == 1) {

                        } else {

                        }
                    }
                });
            }
            changeBackdate();
            document.getElementById('addDebit').addEventListener('click', function() {
                const debitWrapper = document.getElementById('div-debet');
                const newRow = document.createElement('div');
                iRowDebet++;
                newRow.id = 'debet' + (iRowDebet);
                newRow.classList.add('row', 'g-2', 'mb-2', 'rowdebet');
                newRow.innerHTML = `
            <div class="col-md-4 ">
              <select id="dcodegroup${iRowDebet}" onchange="changeCode('debet','${iRowDebet}')" class="form-select select-coa"> ">
               
              </select>
            </div>
            <div class="col-md-4">
              <input id="dnote${iRowDebet}" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="damount${iRowDebet}" type="text" class="form-control currency-input" placeholder="Amount">
            </div>
          `;
                debitWrapper.appendChild(newRow);
                initItemSelectManual('.select-coa', '{{ url('admin/master/chart-account/get-item') }}',
                    'chart account');

            });

            document.getElementById('addKredit').addEventListener('click', function() {
                const kreditWrapper = document.getElementById('div-kredit');
                const newRow = document.createElement('div');
                iRowKredit++;
                newRow.id = 'kredit' + (iRowKredit);
                newRow.classList.add('row', 'g-2', 'mb-2', 'rowkredit');
                newRow.innerHTML = `
            <div class="col-md-4">
              <select id="kcodegroup${iRowKredit}" onchange="changeCode('kredit','${iRowKredit}')" class="form-select select-coa">
               
              </select>
            </div>
            <div class="col-md-4">
              <input id="knote${iRowKredit}" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="kamount${iRowKredit}" type="text" class="form-control currency-input" placeholder="Amount">
            </div>
          `;
                kreditWrapper.appendChild(newRow);
                initItemSelectManual('.select-coa', '{{ url('admin/master/chart-account/get-item') }}',
                    'chart account');

            });


            function changeBackdate() {
                if ($('#is_backdate').is(':checked')) {
                    $('.d-backdate').removeClass('hidden');
                } else {
                    $('.d-backdate').addClass('hidden');
                }

            }

            function openCardCreate() {
                $('#card-create').toggleClass('open');
                $('#icon-create').toggleClass('open');
            }

            function openCardClosing() {
                $('#card-closing').toggleClass('open');
                $('#icon-closing').toggleClass('open');
            }

            function changeCode(type, id) {
                nametype = type == 'debet' ? 'd' : 'k';
                codeGroup = $('#' + nametype + 'codegroup' + id + ' option:selected').val();
                console.log(codeGroup);
                if (codeGroup > 400000) {
                    //kita tambahkan toko_id kalau codegroup >400000
                    html = `<div class="col-md-3 col-toko form-contorl"> <select id="${nametype}toko_id${id}" ></select></div>`;
                    if (type == 'debet') {
                        $('#debet' + id).find('.col-md-4').addClass('col-md-3').removeClass('col-md-4');
                        if ($('#debet' + id).find('.col-toko').length == 0) {
                            $('#debet' + id).append(html);
                        }
                    } else {
                        $('#kredit' + id).find('.col-md-4').addClass('col-md-3').removeClass('col-md-4');
                        if ($('#kredit' + id).find('.col-toko').length == 0) {
                            $('#kredit' + id).append(html);
                        }
                    }
                    initItemSelectManual('#' + nametype + 'toko_id' + id, '{{ url('admin/master/toko/get-item') }}',
                        'Pilih Toko');
                }

            }

            setTimeout(getListMutasiJurnal, 100);


            function prevMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month--;
                if (month < 1) {
                    month = 12;
                    year--;
                }
                window.location.href = '{{ url('admin/jurnal/mutasi') }}?month=' + month + '&year=' + year;
            }

            function nextMonth() {
                month = '{{ $month }}';
                year = '{{ $year }}';
                month++;
                if (month > 12) {
                    month = 1;
                    year++;
                }
                window.location.href = '{{ url('admin/jurnal/mutasi') }}?month=' + month + '&year=' + year;
            }

            function submitJournalManual() {
                isBackdate = $('#is_backdate').is(':checked') == true ? 1 : 0;
                date = isBackdate == 1 ? $('#date-mutasi-journal').val() : $('#date-mutasi').val();
                type = null;
                debets = [];
                kredits = [];
                $('.rowdebet').each(function(i, elem) {
                    id = getNumID($(elem).attr('id'));
                    codeGroup = $('#dcodegroup' + id + ' option:selected').val();
                    note = $('#dnote' + id).val();
                    amount = formatDB($('#damount' + id).val());
                    toko_id = $('#dtoko_id' + id).val();
                    if (toko_id == undefined) {
                        toko_id = null;
                    }
                    debets.push({
                        code_group: codeGroup,
                        description: note,
                        amount: amount,
                        reference_id: null,
                        reference_type: null,
                        toko_id: toko_id
                    });
                });
                $('.rowkredit').each(function(i, elem) {
                    id = getNumID($(elem).attr('id'));
                    codeGroup = $('#kcodegroup' + id + ' option:selected').val();
                    note = $('#knote' + id).val();
                    amount = formatDB($('#kamount' + id).val());
                    toko_id = $('#ktoko_id' + id).val();
                    if (toko_id == undefined) {
                        toko_id = null;
                    }
                    kredits.push({
                        code_group: codeGroup,
                        description: note,
                        amount: amount,
                        reference_id: null,
                        reference_type: null,
                        toko_id: toko_id
                    });
                });
                data = {
                    date: date,
                    type: type,
                    debets: debets,
                    kredits: kredits,
                    is_auto_geterated: 0,
                    is_backdate: isBackdate,
                    user_backdate_id: '{{ user()->id }}',
                    url_try_again: "tidak tersedia",
                    _token: '{{ csrf_token() }}'
                };
                console.log(data);

                Swal.fire({
                    title: "Apakah kamu yakin?",
                    text: "Data akan diproses!",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    allowOutsideClick: false,
                    showLoaderOnConfirm: true, // Loader muncul saat klik Yes
                    preConfirm: () => {
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '{{ url('admin/jurnal/submit-manual') }}',
                                method: 'post',
                                data: data,
                                success: function(res) {
                                    console.log(res);
                                    if (res.status == 1) {
                                        Swal.fire('success', 'journal sudah tercreate on ' + res
                                            .journal_number, 'success');
                                    } else {
                                        Swal.fire('opps', res.msg, 'error');
                                    }
                                },
                                error: function(res) {
                                    Swal.fire("opps", "something error", 'error');
                                }
                            });
                        });
                    },
                    // allowOutsideClick: () => !Swal.isLoading() // Mencegah klik di luar saat loading
                });


            }

            function halamanChange() {
                page = $('#halaman-input').val();
                getListMutasiJurnal(page);
            }

            function getListMutasiJurnal(page = "") {
                $.ajax({
                    url: '{{ url('admin/jurnal/get-list-mutasi') }}?page=' + page +
                        '&year={{ $year }}&month={{ $month }}',
                    type: 'GET',
                    success: function(res) {
                        if (res.status == 1) {
                            renderListMutasiJurnal(res);
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });

            }

            function renderListMutasiJurnal(res) {
                html = "";
                console.log(res);
                $('#max-page').html('of ' + res.max_page);
                Object.keys(res.msg).forEach(function eachJournalNumber(journalNumber, i) {
                    rowspan = res.msg[journalNumber].length;
                    res.msg[journalNumber].forEach(function eachJournal(journal, j) {
                        tanggal = formatNormalDateTime(new Date(journal.created_at));
                        if (j == 0) {
                            html += `
              <tr>
                <td class="text-center" rowspan="${rowspan}">${i+1}</td>
                <td rowspan="${rowspan}">${tanggal}</td>
                <td rowspan="${rowspan}">${journal.journal_number} [${journal.id}]</td>
                <td>${journal.code_group} - ${res.chart_accounts[journal.code_group]}</td>
                <td>${journal.description}</td>
                <td class="text-end">${formatRupiah(journal.amount_debet)}</td>
                <td class="text-end">${formatRupiah(journal.amount_kredit)}</td>
                <td> 
                     ${journal.verified_by == 1 ? '<span class="bg-primary p-1 fs-7 rounded-1 colorwhite" ><i class="fas fa-check"></i> valid </span>' : '<span class="bg-warning fs-7 p-1 rounded-1 colorwhite"><i class="fas fa-times"></i> invalid</span>'}
                     <button class="btn btn-secondary btn-sm" onclick="verifyJournal(${journal.id})" > <i class="fas fa-refresh"></i></button>
                     <button class="btn btn-danger btn-sm" onclick="deleteJournal(${journal.id})" > <i class="fas fa-trash"></i></button>
                </td>
               
               </tr>
            `;
                        } else {
                            html += `
              <tr>
                <td>${journal.code_group} - ${res.chart_accounts[journal.code_group]}</td>
                <td>${journal.description}</td>
                <td class="text-end">${formatRupiah(journal.amount_debet)}</td>
                <td class="text-end">${formatRupiah(journal.amount_kredit)}</td>
                <td>
                   ${journal.verified_by == 1 ? '<span class="bg-primary p-1 fs-7 rounded-1 colorwhite " ><i class="fas fa-check"></i> valid </span>' : '<span class="bg-warning fs-7 p-1 rounded-1 colorwhite"><i class="fas fa-times"></i> invalid</span>'}
                   <button class="btn btn-secondary btn-sm" onclick="verifyJournal(${journal.id})" > <i class="fas fa-refresh"></i></button>
                    <button class="btn btn-danger btn-sm" onclick="deleteJournal(${journal.id})" > <i class="fas fa-trash"></i></button>
                </td>
              </tr>
            `;
                        }
                    });
                });
                $('#body-mutasi-jurnal').html(html);
            }

            function deleteJournal(id) {
                url = '{{ route('jurnal.delete', ['id' => '__id__']) }}';
                url = url.replace('__id__', id);
                console.log(url);
                swalDelete({
                    url: url,
                    successText: "Delete berhasil!",
                    onSuccess: (res) => {

                    }
                });
            }

            function verifyJournal(id) {
                $.ajax({
                    url: '{{ url('admin/jurnal/verify') }}/' + id,
                    type: 'get',
                    success: function(res) {
                        if (res.status == 1) {
                            Swal.fire('success', 'journal sudah terverifikasi', 'success');
                            getListMutasiJurnal();
                        } else {
                            Swal.fire('opps', res.msg, 'error');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            }

            setTimeout(getTaskImport, 100);


            function trySearch() {
                getListMutasiJurnal();
            }

            function getTaskImport() {
                $.ajax({
                    url: '{{ url('admin/jurnal/get-task-import-aktif') }}',
                    method: 'get',
                    success: function(res) {
                        console.log(res);
                        if (res.status == 1) {
                            html = "";
                            res.msg.forEach(function(item) {
                                html += ` <div class="row p-2">
               <a href="{{ url('admin/jurnal/get-import-saldo-followup') }}/${item.id}">
                  <div class="col-xs-12 bg-primary p-2 rounded-3">
                  <p class="text-white fs-5 mb-0"><strong>${item.description}</strong></p> 
                  <p class="text-white fs-7 mb-2">${item.resume_string}</p> 
                  </div>
                </a>

              </div>`;
                            });
                            $('#container-task').html(html);
                        } else {

                        }
                    },
                    error: function(res) {


                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
