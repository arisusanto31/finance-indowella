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
        <div class="col-md-2">
          <select id="coa" class="form-select select-coa"></select>

        </div>
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
          <button onclick="searchData()" class="btn btn-primary btn-sm w-100">Cari</button>
        </div>
      </div>



      <div class="table-responsive mt-2">
        <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
          <thead class="bg-white text-dark text-center">
            <tr>
              <th>No</th>
              <th>📅 Tanggal</th>
              <th>#️⃣ No Jurnal</th>
              <th>🔢 LAWAN COA</th>
              <th>📎 Description</th>
              <th>📥 Debet</th>
              <th>📤 Kredit</th>
              <th>💲saldo</th>
            </tr>
          </thead>
          <tbody id="body-mutasi-bukubesar">
          </tbody>
        </table>
      </div>

    </div>
  </div>

  @push('scripts')
  <script>
    initItemSelectManual('.select-coa', '{{route("chart-account.get-item")}}', 'chart account');

    function searchData() {
      month = $('#month').val();
      year = $('#year').val();
      coa = $('#coa option:selected').val();
      $.ajax({
        url: '{{url("admin/jurnal/get-buku-besar")}}?coa=' + coa + '&month=' + month + '&year=' + year,
        method: 'get',
        success: function(res) {
          console.log(res);
          if (res.status == 1) {
            html = '';

            if (res.msg.length == 0) {
              html += `
                  <tr>
                    <td colspan="7" class="text-center">🤷‍♂️ Tidak ada data</td>
                  </tr>
                `;
            }
            res.msg.forEach((item, index) => {
              tanggal = formatNormalDateTime(new Date(item.created_at));

              html += `
                  <tr>
                    <td>${index+1}</td>
                    <td>${tanggal}</td>
                    <td>${item.journal_number} [${item.id}] </td>
                    <td>${item.lawan_code_group} - ${res.chart_accounts[item.lawan_code_group]}</td>
                    <td>${item.description}</td>
                    <td>${formatRupiah(item.amount_debet)}</td>
                    <td>${formatRupiah(item.amount_kredit)}</td>
                    <td>${formatRupiah(item.amount_saldo)}</td>
                  </tr>
                `;
            });
            $('#body-mutasi-bukubesar').html(html);
          } else {
            Swal.fire('opps', res.msg, 'error');
          }
        },
        error: function(res) {
          Swal.fire('opps', 'Gagal mendapatkan data', 'error');
        }
      });
    }
  </script>
  @endpush
</x-app-layout>