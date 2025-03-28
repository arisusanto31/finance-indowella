<x-app-layout>


  <div class="card shadow-sm rounded-3 ">

    <h5 class="text-primary-dark card-header"> <a href="javascript:void(openCardCreate())">⚒️ <strong>BUAT MUTASI JURNAL
        </strong>
        <i id="icon-create" class="bx bx-caret-down toggle-icon"></i> </a>
    </h5>

    <div id="card-create"  class="tree-toggle">

      <input type="hidden" value="{{Date('Y-m-d H:i:s')}}" id="date-mutasi"/>
      <div class="card-body"  style="padding-top: 0px;">
        <div>
          Debit
          <button type="button" class="btn btn-sm btn-success ms-2" id="addDebit">+tambah</button>
        </div>
        <div id="div-debet" class="debet-wrapper">
          <div id="debet1" class="row rowdebet g-2 mb-2 ">
            <div class="col-md-4">
              <select id="dcodegroup1" class="form-control select-coa">
              </select>
            </div>
            <div class="col-md-4">
              <input id="dnote1" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="damount1" type="number" class="form-control" placeholder="Amount">
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
              <select id="kcodegroup1" class="form-control select-coa">

              </select>
            </div>
            <div class="col-md-4">
              <input id="knote1" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="kamount1" type="number" class="form-control" placeholder="Amount">
            </div>
          </div>
        </div>


        <div class="mt-4">
          <button onclick="submitJournalManual()" class="btn btn-primary w-100">Submit Journal</button>
        </div>
      </div>

    </div>
  </div>


  <div class="card mt-3">

    <h5 class="text-primary-dark card-header"> 📤 <strong>MUTASI JURNAL </strong> </h5>
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
              <th>📥 Masuk</th>
              <th>📤 Keluar</th>
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
      initItemSelectManual('.select-coa', '{{url("admin/chart-account/get-item")}}', 'chart account');

    }, 100);
    document.getElementById('addDebit').addEventListener('click', function() {
      const debitWrapper = document.getElementById('div-debet');
      const newRow = document.createElement('div');
      iRowDebet++;
      newRow.id= 'debet' + (iRowDebet);
      newRow.classList.add('row', 'g-2', 'mb-2', 'rowdebet');
      newRow.innerHTML = `
            <div class="col-md-4">
              <select id="dcodegroup${iRowDebet}" class="form-select select-coa"> ">
               
              </select>
            </div>
            <div class="col-md-4">
              <input id="dnote${iRowDebet}" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="damount${iRowDebet}" type="number" class="form-control" placeholder="Amount">
            </div>
          `;
      debitWrapper.appendChild(newRow);
      initItemSelectManual('.select-coa', '{{url("admin/chart-account/get-item")}}', 'chart account');

    });

    document.getElementById('addKredit').addEventListener('click', function() {
      const kreditWrapper = document.getElementById('div-kredit');
      const newRow = document.createElement('div');
      iRowKredit++;
      newRow.id= 'kredit' + (iRowKredit);
      newRow.classList.add('row', 'g-2', 'mb-2', 'rowkredit');
      newRow.innerHTML = `
            <div class="col-md-4">
              <select id="kcodegroup${iRowKredit}" class="form-select select-coa">
               
              </select>
            </div>
            <div class="col-md-4">
              <input id="knote${iRowKredit}" type="text" class="form-control" placeholder="Note">
            </div>
            <div class="col-md-4">
              <input id="kamount${iRowKredit}" type="number" class="form-control" placeholder="Amount">
            </div>
          `;
      kreditWrapper.appendChild(newRow);
      initItemSelectManual('.select-coa', '{{url("admin/chart-account/get-item")}}', 'chart account');

    });

    function openCardCreate() {
      $('#card-create').toggleClass('open');
      $('#icon-create').toggleClass('open');
    }

    setTimeout(getListMutasiJurnal, 100);


    function submitJournalManual() {
      date = $('#date-mutasi').val();
      type = null;
      debets = [];
      kredits = [];
      $('.rowdebet').each(function(i, elem) {
        id = getNumID($(elem).attr('id'));
        codeGroup = $('#dcodegroup' + id + ' option:selected').val();
        note = $('#dnote' + id).val();
        amount = $('#damount' + id).val();
        debets.push({
          code_group: codeGroup,
          description: note,
          amount: amount,
          reference_id: null,
          reference_type: null,
        });
      });
      $('.rowkredit').each(function(i, elem) {
        id = getNumID($(elem).attr('id'));
        codeGroup = $('#kcodegroup' + id + ' option:selected').val();
        note = $('#knote' + id).val();
        amount = $('#kamount' + id).val();
        kredits.push({
          code_group: codeGroup,
          description: note,
          amount: amount,
          reference_id: null,
          reference_type: null,
        });
      });
      data = {
        date: date,
        type: type,
        debets: debets,
        kredits: kredits,
        is_auto_geterated: 0,
        is_backdate: $('#is-backdate').is(':checked') == true ? 1 : 0,
        user_backdate_id: '{{user()->id}}',
        url_try_again: "tidak tersedia",
        _token: '{{csrf_token()}}'
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
              url: '{{url("admin/jurnal/submit-manual")}}',
              method: 'post',
              data: data,
              success: function(res) {
                console.log(res);
                if (res.status == 1) {
                  Swal.fire('success', 'journal sudah tercreate on ' + res.journal_number,'success');
                } else {
                  Swal.fire('opps', res.msg,'error');
                }
              },
              error: function(res) {
                Swal.fire("opps", "something error",'error');
              }
            });
          });
        },
        // allowOutsideClick: () => !Swal.isLoading() // Mencegah klik di luar saat loading
      });


    }

    function getListMutasiJurnal() {
      $.ajax({
        url: '{{url("admin/jurnal/get-list-mutasi")}}',
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
      html="";
      console.log(res);
      Object.keys(res.msg).forEach(function eachJournalNumber(journalNumber, i) {
        rowspan = res.msg[journalNumber].length;
        res.msg[journalNumber].forEach(function eachJournal(journal, j) {
          tanggal= formatNormalDateTime(new Date(journal.created_at));
          if (j == 0) {
            html += `
              <tr>
                <td class="text-center" rowspan="${rowspan}">${i+1}</td>
                <td rowspan="${rowspan}">${tanggal}</td>
                <td rowspan="${rowspan}">${journal.journal_number}</td>
                <td>${journal.code_group}</td>
                <td>${journal.description}</td>
                <td class="text-end">${formatRupiah(journal.amount_debet)}</td>
                <td class="text-end">${formatRupiah(journal.amount_kredit)}</td>
              </tr>
            `;
          } else {
            html += `
              <tr>
                <td>${journal.code_group}</td>
                <td>${journal.description}</td>
                <td class="text-end">${formatRupiah(journal.amount_debet)}</td>
                <td class="text-end">${formatRupiah(journal.amount_kredit)}</td>
              </tr>
            `;
          }
        });
      });
      $('#body-mutasi-jurnal').html(html);
    }
  </script>
  @endpush
</x-app-layout>