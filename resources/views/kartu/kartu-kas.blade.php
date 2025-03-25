<x-app-layout>

   
      
    <div class="container mt-5">
        <div id="kartuKasTable_length"></div>
      <div class="row mb-3">
        <div class="col-md-2 ms-auto text-end">
          <input type="text" id="customSearch" class="form-control" placeholder="🔍 Cari transaksi...">
        </div>
      </div>
  
      <!-- Tabel Kartu Kas -->
      <div class="table-responsive">
        <table id="kartuKasTable" class="table table-bordered table-striped table-hover align-middle">
          <thead class="bg-white text-dark text-center">
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Keterangan</th>
              <th>No Bukti</th>
              <th>Description</th>
              <th>Masuk</th>
              <th>Keluar</th>
              <th>Saldo</th>
            </tr>
            
          </thead>
          <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Andi Prasetyo</td>
                <td>12.345.678.9-012.000</td>
                <td>Akuntan</td>
                <td class="text-end">Rp7.000.000</td>
                <td class="text-end">Rp200.000</td>
                <td class="text-end">60000</td>
                <td class="text-end">7000</td>
              </tr>
              <tr>
                <td class="text-center">2</td>
                <td>Ari Prasetyo</td>
                <td>12.345.678.9-012.000</td>
                <td>HAlO</td>
                <td class="text-end">Rp7.000.000</td>
                <td class="text-end">Rp200.000</td>
                <td class="text-end">60000</td>
                <td class="text-end">7000</td>
              </tr>
              <tr>
                <td class="text-center">3</td>
                <td>HANDI</td>
                <td>12.345.678.9-012.000</td>
                <td>gudang</td>
                <td class="text-end">Rp7.000.000</td>
                <td class="text-end">Rp200.000</td>
                <td class="text-end">60000</td>
                <td class="text-end">7000</td>
              </tr>
          </tbody>
        </table>
      </div>
    </div>
  
   
<script>
    $(document).ready(function () {
        const table = $('#kartuKasTable').DataTable({
          pageLength: 10, // default awal
          lengthChange: true, // ini aktifkan dropdown "Show entries"
          dom: '<"row mb-3"<"col-md-6"l><"col-md-6 text-end"f>>rt<"row mt-3"<"col-md-6"i><"col-md-6 text-end"p>>',
          language: {
            lengthMenu: "Tampilkan _MENU_ entri",
            search: "🔍 Cari:",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
            paginate: {
              previous: "←",
              next: "→"
            }
          },
          columnDefs: [
            { targets: [3, 4, 5, 6, 7], className: 'text-end' }
          ]
        });
      
        // Custom input search jika ada
        $('#customSearch').on('keyup', function () {
          table.search(this.value).draw();
        });
      });
    </script>
  
  </x-app-layout>
  