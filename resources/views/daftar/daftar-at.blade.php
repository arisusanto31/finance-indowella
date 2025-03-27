<x-app-layout>
  <style>
    th, td {
      white-space: nowrap;
      background-color: white !important;
    }

   
    .sticky-col-1 {
      position: sticky;
      left: 0;
      width: 50px;
      min-width: 50px;
      max-width: 50px;
      z-index: 5;
      text-align: center;
    }

    .sticky-col-2 {
      position: sticky;
      left: 50px;
      width: 180px;
      min-width: 180px;
      max-width: 180px;
      z-index: 6;
    }

    .sticky-col-3 {
      position: sticky;
      left: 230px; 
      width: 80px;
      min-width: 80px;
      max-width: 80px;
      z-index: 7;
      text-align: center;
    }

    .sticky-col-4 {
      position: sticky;
      left: 310px; 
      width: 100px;
      min-width: 100px;
      max-width: 100px;
      z-index: 8;
      text-align: center;
    }
  </style>

  <div class="p-4">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th class="sticky-col-1">No</th>
            <th class="sticky-col-2">Nama Barang</th>
            <th class="sticky-col-3">Qty</th>
            <th class="sticky-col-4">Tahun</th>
            <th>Bulan</th>
            <th>Nilai Perolehan</th>
            <th>Akumulasi Penyusutan</th>
            <th>Nilai Buku</th>
            <th>Penyusutan Jan</th>
            <th>Penyusutan Feb</th>
            <th>Penyusutan Mar</th>
            <th>Penyusutan Apr</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="sticky-col-1">1</td>
            <td class="sticky-col-2">Printer Sharkpos</td>
            <td class="sticky-col-3">2</td>
            <td class="sticky-col-4">2025</td>
            <td class="sticky-col-5">Feb</td>
            <td>474.000</td>
            <td>0</td>
            <td>474.000</td>
            <td>9.875</td>
            <td>9.875</td>
            <td>9.875</td>
            <td>9.875</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
