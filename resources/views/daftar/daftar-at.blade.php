<x-app-layout>
    <style>
        .table-responsive {
          overflow-x: auto;
          -webkit-overflow-scrolling: touch;
        }
      </style>
      
      <div class="container mt-3">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-header bg-white text-white rounded-top-4">
            <h4 class="mb-0 text-black">📋 Table Biaya Dibayar di Muka (BDD)</h4>
          </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-primary text-center">
              <tr>
                <th>Tanggal Perolehan</th>
                <th>Nama Aset</th>
                <th>Kode Aset</th>
                <th>Lokasi</th>
                <th>Harga Perolehan</th>
                <th>Umur Ekonomis</th>
                <th>Akumulasi Penyusutan</th>
                <th>Nilai Buku</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>2022-01-01</td>
                <td>Laptop ASUS ROG</td>
                <td>AT-001</td>
                <td>Kantor Indowella</td>
                <td class="text-end">Rp15.000.000</td>
                <td class="text-center">4 Tahun</td>
                <td class="text-end">Rp7.500.000</td>
                <td class="text-end">Rp7.500.000</td>
              </tr>
              <tr>
                <td>2021-06-01</td>
                <td>Mesin Cetak</td>
                <td>AT-002</td>
                <td>Gedung A1</td>
                <td class="text-end">Rp50.000.000</td>
                <td class="text-center">5 Tahun</td>
                <td class="text-end">Rp30.000.000</td>
                <td class="text-end">Rp20.000.000</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      

</x-app-layout>
