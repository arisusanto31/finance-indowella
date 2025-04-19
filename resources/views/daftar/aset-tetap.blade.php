<x-app-layout>


  @push('styles')
  <style>
    table.table.sticky-table {
      border-collapse: separate;
      border-spacing: 0;
      width: max-content;
      min-width: 1500px;
      table-layout: fixed;
      background-color: white;
    }

    table.sticky-table th,
    table.sticky-table td {
      border: 1px solid #dee2e6;
      box-sizing: border-box;
      padding: 0.5rem;
      white-space: nowrap;
      vertical-align: middle;
      text-align: center;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    thead.table-light {
      color: white;
    }

    /* Sticky columns */
    .sticky-col-1,
    .sticky-col-2,
    .sticky-col-3,
    .sticky-col-4,
    .sticky-col-5 {
      position: sticky;

      z-index: 30;
      border-right: 1px solid #dee2e6;
    }

    td.sticky-col-1,
    td.sticky-col-2,
    td.sticky-col-3,
    td.sticky-col-4,
    td.sticky-col-5 {
      background-color: #f8f9fa !important;
    }

    .sticky-col-1 {
      left: 0px;
      width: 60px;
      min-width: 60px;
      max-width: 60px;
    }

    .sticky-col-2 {
      left: 60px;
      width: 200px;
      min-width: 200px;
      max-width: 200px;
    }

    .sticky-col-3 {
      left: 260px;
      width: 100px;
      min-width: 100px;
      max-width: 100px;
    }

    .sticky-col-4 {
      left: 360px;
      width: 150px;
      min-width: 150px;
      max-width: 150px;
    }

    .sticky-col-5 {
      left: 510px;
      width: 150px;
      min-width: 150px;
      max-width: 150px;
    }
  </style>

  @endpush

  <div class="card shadow-sm mb-4">
    <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 💳 <strong>ASET TETAP</strong> </h5>
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
      </div>

      <div class="row">
        <div class="col-xl-12 col-md-12">

          <div class="nav-align-top mb-4">
            <ul class="nav nav-pills mb-3" role="tablist">
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link active"
                  role="tab"
                  data-bs-toggle="tab"
                  data-bs-target="#navs-pills-top-home"
                  aria-controls="navs-pills-top-home"
                  aria-selected="true"
                  onclick="getSummary()">
                  🗃 KARTU
                </button>
              </li>
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link"
                  role="tab"
                  data-bs-toggle="tab"
                  data-bs-target="#navs-pills-top-profile"
                  aria-controls="navs-pills-top-profile"
                  aria-selected="false"
                  onclick="getMutasiMasuk()">
                  📥 Masuk
                </button>
              </li>
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link"
                  role="tab"
                  data-bs-toggle="tab"
                  data-bs-target="#navs-pills-top-messages"
                  aria-controls="navs-pills-top-messages"
                  aria-selected="false"
                  onclick="getMutasiKeluar()">
                  📤 Keluar
                </button>
              </li>
            </ul>
            <div class="tab-content" style="background-color: #f8f9fa;">
              <div class="tab-pane fade show active" id="navs-pills-top-home" role="tabpanel">
                <div class="row mt-1">
                  <div class="col-md-2">
                    <button type="button" class=" btn-primary" onclick="showModalInventory()"> 🔃 buat inventory</button>
                  </div>
                </div>
                <div id="div-table" class="table-responsive mt-2">

                </div>
              </div>
              <div class="tab-pane fade" id="navs-pills-top-profile" role="tabpanel">

                <div class="table-responsive mt-2">

                  <table id="kartuMasuk" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                      <tr>
                        <th>No</th>
                        <th>📅Tanggal</th>
                        <th>Inventory</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Journal Number</th>
                      </tr>

                    </thead>
                    <tbody id="body-mutasi-masuk">
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-pills-top-messages" role="tabpanel">
                <div class="row mt-1">
                  <div class="col-md-2">
                    <button type="button" class=" btn-primary" onclick="showModalKartuInventory()"> 🔃 buat kartu</button>
                  </div>
                </div>

                <div class="table-responsive mt-2">
                  <table id="kartuKeluar" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                      <tr>
                        <th>No</th>
                        <th>📅Tanggal</th>
                        <th>Inventory</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Journal Number</th>
                      </tr>

                    </thead>
                    <tbody id="body-mutasi-keluar">
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modal-journal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel1">Buat Link ke Jurnal</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div id="keterangan-kartu" class="col mb-3">
            </div>
          </div>
          <div class="row">
            <div class="col-md-12 col-xs-12">
              <label>Cari Jurnal</label>
            </div>
            <div class="col">
              <select class="form-control" id="select-code_group">

              </select>
            </div>
            <div class="col">
              <input type="text" id="daterange" class="form-control" placeholder="Pilih Tanggal" />
            </div>


          </div>
          <div class="row">
            <div class="col">
              <input type="text" id="description" placeholder="cari deskripsi" class="form-control" />
            </div>
            <div class="col">
              <button type="button" class="btn btn-primary" onclick="searchJournal()">Cari</button>
            </div>
          </div>
          <div class="row p-2 m-1" style="background-color:#eee" id="container-journal">

          </div>
          <input type="hidden" id="journal_id" />
          <input type="hidden" id="model_id" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Close
          </button>
          <button id="btn-store-pelunasan" onclick="linkJournal()" type="button" class="btn btn-primary">LINK !!</button>
        </div>
      </div>
    </div>
  </div>


  @push('scripts')
  <script>
    var page = "kartu";

    function showModalInventory() {
      showDetailOnModal('{{route("aset-tetap.create")}}');
    }

    function showModalKartuInventory() {
      showDetailOnModal('{{route("aset-tetap.create-kartu")}}');
    }


    function arrayPad(arr, length, padValue) {
      const diff = Math.abs(length) - arr.length;
      if (diff <= 0) return arr.slice(); // sudah cukup panjang

      const padding = new Array(diff).fill(padValue);

      return length > 0 ?
        arr.concat(padding) // pad ke kanan
        :
        padding.concat(arr); // pad ke kiri
    }

    function normalizeTable(table) {
      const count = table.headers.length;

      // Normalisasi setiap row di 'data'
      table.data = table.data.map(row => {
        return arrayPad(row, count, '-');
      });

      // Normalisasi subtotal
      table.subtotal = arrayPad(table.subtotal, count, '-');
    }
    var year = '{{$year}}';
    var headerTabel = [
      'No',
      'NAMA aset',
      'Qty',
      'Periode',
      'Nilai Perolehan',
      'Mutasi Pembelian',
      'Penyusutan ' + year + '-' + '01',
      'Penyusutan ' + year + '-' + '02',
      'Penyusutan ' + year + '-' + '03',
      'Penyusutan ' + year + '-' + '04',
      'Penyusutan ' + year + '-' + '05',
      'Penyusutan ' + year + '-' + '06',
      'Penyusutan ' + year + '-' + '07',
      'Penyusutan ' + year + '-' + '08',
      'Penyusutan ' + year + '-' + '09',
      'Penyusutan ' + year + '-' + '10',
      'Penyusutan ' + year + '-' + '11',
      'Penyusutan ' + year + '-' + '12',
      'Akumulasi Penyusutan',
      'Nilai Buku'
    ];

    var dataMutasi = [];

    function getSummary() {
      page = "kartu";
      $('#div-table').html('');
      $.ajax({
        url: '{{route("aset-tetap.get-summary")}}',
        method: 'get',
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            Object.keys(res.msg).forEach(function eachData(type) {
              dataType = res.msg[type];
              varHeader = headerTabel;
              stringHeader = "";
              varHeader.forEach(function eachHeader(header, i) {
                sticky = i < 5 ? 'sticky-col-' + (i + 1) + ' bg-primary-dark' : 'bg-primary-light';
                stringHeader += '<th class="' + sticky + ' text-white">' + header + '</th>';
              });
              stringData = "";
              Object.keys(dataType).forEach(function eachInv(invID, indexInv) {
                dataInv = dataType[invID];
                stringData += `
                <tr>
                  <td class="sticky-col-1">${indexInv + 1}</td>
                  <td class="sticky-col-2">${dataInv.name}</td>
                  <td class="sticky-col-3">${dataInv.keterangan_qty_unit}</td>
                  <td class="sticky-col-4">${dataInv.periode} tahun</td>
                  <td class="sticky-col-5">${formatRupiah(dataInv.nilai_perolehan)}</td>
                  <td>${formatRupiah(dataInv.total_pembelian)}</td>
                  <td>${array_key_exists(year+'-01',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-01'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-02',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-02'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-03',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-03'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-04',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-04'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-05',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-05'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-06',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-06'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-07',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-07'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-08',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-08'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-09',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-09'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-10',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-10'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-11',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-11'].total_penyusutan) : '-'}</td>
                  <td>${array_key_exists(year+'-12',dataInv.penyusutan) ? formatRupiah(dataInv.penyusutan[year+'-12'].total_penyusutan) : '-'}</td>
                  <td>${formatRupiah(dataInv.total_penyusutan)}</td>
                  <td>${array_key_exists(invID,res.saldo_buku_akhir)?formatRupiah(res.saldo_buku_akhir[invID].nilai_buku):0}</td>
                </tr>`;


              });
              stringTable = `
                      <h5 class="mb-1 mt-2 text-primary-dark"><strong>${type}</strong></h4>
                      <div style="max-height: 400px; overflow-x: auto; overflow-y: auto;">
                                    <table class="table sticky-table table-bordered">
                                      <thead class="table-light">
                                        <tr>
                                          ${stringHeader}
                                        </tr>
                                      </thead>
                                      <tbody>
                                        ${stringData}      
                                       
                                      </tbody>
                                    </table>
                      </div>`;
              $('#div-table').append(stringTable);
            });
          } else {
            Swal.fire('ops', 'something error ' + res.msg, 'error');
          }
        },
        error: function(err) {
          console.log(err);
        }
      });
    }


    getSummary();

    function getMutasiKeluar() {
      page = "keluar";
      $.ajax({
        url: '{{route("aset-tetap.get-mutasi-keluar")}}',
        method: 'get',
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            var stringData = "";
            res.msg.forEach(function eachData(data, i) {
              dataMutasi[data.id] = data;
              stringData += `<tr>
                <td>${i+1}</td>
                <td>${data.date}</td>
                <td>${data.name}</td>
                <td>${data.type_aset}</td>
                <td>${formatRupiah(data.amount)}</td>
                <td>${(!data.journal_number?'<span> belum ada jurnal</span> <button onclick="openLinkJournal('+data.id+')"> <i class="fas fa-link"></i> jurnal</button>':data.journal_number)}</td>
              </tr>`;
            });
            $('#body-mutasi-keluar').html(stringData);
          } else {
            Swal.fire('ops', 'something error ' + res.msg, 'error');
          }
        },
        error: function(err) {
          console.log(err);
        }
      });
    }

    function getMutasiMasuk() {
      page = "masuk";
      $.ajax({
        url: '{{route("aset-tetap.get-mutasi-masuk")}}',
        method: 'get',
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            var stringData = "";
            res.msg.forEach(function eachData(data, i) {
              dataMutasi[data.id] = data;

              stringData += `<tr>
                <td>${i+1}</td>
                <td>${data.date}</td>
                <td>${data.name}</td>
                <td>${data.type_aset}</td>
                <td>${formatRupiah(data.amount)}</td>
                <td>${(!data.journal_number?'<span> belum ada jurnal</span> <button onclick="openLinkJournal('+data.id+')"> <i class="fas fa-link"></i> jurnal</button>':data.journal_number)}</td>
                           
              </tr>`;
            });
            $('#body-mutasi-masuk').html(stringData);
          } else {
            Swal.fire('ops', 'something error ' + res.msg, 'error');
          }
        },
        error: function(err) {
          console.log(err);
        }
      });
    }

    $('#daterange').daterangepicker({
      opens: 'right',
      locale: {
        format: 'YYYY-MM-DD'
      }
    });
    console.log('init berhasil lur ');
    initItemSelectManual('#select-code_group', '{{route("chart-account.get-item-keuangan")}}?kind=kartu-inventory', 'pilih kode akun', '#modal-journal');

    function openLinkJournal(id) {
      $('#modal-journal').modal('show');
      $('#keterangan-kartu').html(`
         <p>Link Kartu Inventory ID :  ${id}</p>
         <p>${dataMutasi[id].name} - ${dataMutasi[id].type_aset} :${formatRupiah(dataMutasi[id].amount)}</p>
        
        `);
      $('#model_id').val(id);

    }

    function linkJournal() {
      id = $('#journal_id').val();
      if (id == "") {
        Swal.fire("opss", "Pilih jurnal terlebih dahulu", "error");
        return;
      }
      model_id = $('#model_id').val();
      if (model_id == "") {
        Swal.fire("opss", "Pilih kartu stock terlebih dahulu", "error");
        return;
      }

      $.ajax({
        url: '{{route("jurnal.link-journal")}}',
        method: 'POST',
        data: {
          "_token": "{{ csrf_token() }}",
          "model_id": model_id,
          "journal_id": id,
          "model": "App\\Models\\KartuInventory",
        },
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            $('#modal-journal').modal('hide');
            swalInfo("Berhasil", "Berhasil menghubungkan jurnal ke kartu inventory", "success");
            if (page == "masuk")
              getMutasiMasuk();
            else if (page == 'keluar') {
              getMutasiKeluar();
            }
          } else {
            Swal.fire("opss", res.msg, "error");
          }
        },
        error: function(err) {
          console.log(err);
        }
      });
    }

    function searchJournal() {

      $.ajax({
        url: '{{route("jurnal.search-error")}}?code_group=' + $('#select-code_group').val() + '&daterange=' + $('#daterange').val() + '&description=' + $('#description').val(),
        method: 'get',
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            html = "";
            res.msg.forEach(function eachData(data) {
              html += `
                    <a href="javascript:void(pilihJurnal(${data.id}))" >
                        <div id="item-jurnal${data.id}" class="col-md-12 col-xs-12 item-jurnal colorblack " style="position:relative; border-bottom:1px solid black;">
                            <span style="position:absolute; top:0px; left:-17px"> <i class="fas fa-circle"></i></span>

                            <label  for="journal_id_${data.id}">${data.journal_number} - ${data.description} - ${formatNormalDateTime(new Date(data.created_at))} : ${formatRupiah(data.amount_debet - data.amount_kredit)}</label>
                        </div>
                    </a>
                `;
            });
            $('#container-journal').html(html);
          } else {

          }
        },
        error: function(res) {
          console.log(res);
        }
      });
    }


    function pilihJurnal(id) {
      $('.item-jurnal').removeClass('bg-primary colorwhite');
      $('#item-jurnal' + id).addClass('bg-primary colorwhite');
      $('#journal_id').val(id);
    }
  </script>


  @endpush




</x-app-layout>