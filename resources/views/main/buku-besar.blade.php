<x-app-layout>

  <style>
    btn-big-custom {
      padding: 20px 40px;
      font-size: 1.5rem;
      border-radius: 8px;
    }
  </style>
  <div class="container-fluid px-4 mt-4">

    {{-- ✅ Card 1: Filter & Tombol Tambah --}}
    <div class="card shadow-sm mb-4">
      <h5 class="text-primary-dark card-header" style="padding-bottom:0px;"> 📚 <strong>BUKU BESAR </strong> </h5>
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
          <!-- <a href="#" class="btn btn-primary btn-big-custom rounded-0">Tambah Jurnal Umum</a> -->
        </div>

        <form method="GET" action="#" class="row g-2">
          <div class="col-md-2">
            <select class="form-select select-coa"></select>

          </div>
          <div class="col-md-2">
            <select name="bulan" class="form-select ">
              <option value="">-- Bulan --</option>
              <option>November</option>
              <option>Desember</option>
              <option>Januari</option>
            </select>
          </div>
          <div class="col-md-2">
            <select name="tahun" class="form-select ">
              <option value="">-- Tahun --</option>
              <option>2023</option>
              <option>2024</option>
              <option>2025</option>
            </select>
          </div>
          <div class="col-md-2">
            <button onclick="searchData()" class="btn btn-primary btn-sm w-100">Cari</button>
          </div>
        </form>



        <div class="table-responsive mt-2">
          <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
            <thead class="bg-white text-dark text-center">
              <tr>
                <th>No</th>
                <th>📅 Tanggal</th>
                <th>#️⃣ No Jurnal</th>
                <th>🔢 LAWAN COA</th>
                <th>📎 Description</th>
                <th>🔃 Mutasi</th>
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

      function searchData(){
        $.ajax({
          url:'{{url("admin/jurnal/get-buku-besar")}}',
          method:'get',
          success:function(res){
            console.log(res);
            if(res.status==1){
              html='';

            }
          },error:function(res){

          }
        });
      }
    </script>
    @endpush
</x-app-layout>