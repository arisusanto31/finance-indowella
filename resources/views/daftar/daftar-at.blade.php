<x-app-layout>


  @push('styles')
  <style>
    table.table {
      border-collapse: separate;
      border-spacing: 0;
      width: max-content;
      min-width: 1500px;
      table-layout: fixed;
      background-color: white;
    }

    table th,
    table td {
      border: 1px solid #dee2e6;
      box-sizing: border-box;
      padding: 0.5rem;
      white-space: nowrap;
      vertical-align: middle;
      text-align: center;
      background-color: white !important;
      color: #000;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    thead.table-light th.bg-primary {
      background-color: #007bff !important;
      color: white;
    }

    /* Sticky columns */
    .sticky-col-1,
    .sticky-col-2,
    .sticky-col-3,
    .sticky-col-4,
    .sticky-col-5 {
      position: sticky;
      background-color: white !important;
      z-index: 30;
      border-right: 1px solid #dee2e6;
    }

    .sticky-col-1 {
      left: 0px;
      width: 60px;
      min-width: 60px;
      max-width: 60px;
    }

    .sticky-col-2 {
      left: 60px;
      width: 180px;
      min-width: 180px;
      max-width: 180px;
    }

    .sticky-col-3 {
      left: 240px;
      width: 80px;
      min-width: 80px;
      max-width: 80px;
    }

    .sticky-col-4 {
      left: 320px;
      width: 100px;
      min-width: 100px;
      max-width: 100px;
    }

    .sticky-col-5 {
      left: 420px;
      width: 100px;
      min-width: 100px;
      max-width: 100px;
    }
  </style>

  @endpush
  @php
  $table1 = [
  'title' => 'Table 1',
  'headers' => ['No', 'Tanah', 'Qty', 'Tahun', 'Bulan', 'Periode', 'Nilai Perolehan', 'Akum. Peny', 'Mutasi Pembelian', 'Nilai Perolehan',
  'Penyusutan Januari', 'Penyusutan Februari', 'Penyusutan Maret', 'Penyusutan April', 'Penyusutan Mei', 'Penyusutan Juni', 'Penyusutan Juli',
  'Penyusutan Agustus','Penyusutan September','Penyusutan Oktober','Penyusutan November','Penyusutan Desember',
  'Total Penyusutan','Total Akumulasi Penyusutan','Nilai Buku','Keterangan'],
  'data' => [
  ['1', 'Printer Sharkpos', 2, 2025, 'Feb', '1 Tahun', '10.000.000', '1.000.000', '0', '10.000.000',
  '100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000',
  '1.200.000', '2.000.000', '8.000.000', 'Aktif']
  ],
  'subtotal' => array_pad(['-', '-', '-', '-', 'Subtotal', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-', '-'], 26, '-'),
  ];

  $table2 = [
  'title' => 'Table 2',
  'headers' => ['No', 'Truck', 'Qty', 'Tahun', 'Bulan', 'Periode', 'Nilai Perolehan', 'Akum. Peny', 'Mutasi Pembelian', 'Nilai Perolehan',
  'Penyusutan Januari', 'Penyusutan Februari', 'Penyusutan Maret', 'Penyusutan April', 'Penyusutan Mei', 'Penyusutan Juni', 'Penyusutan Juli',
  'Penyusutan Agustus','Penyusutan September','Penyusutan Oktober','Penyusutan November','Penyusutan Desember',
  'Total Penyusutan','Total Akumulasi Penyusutan','Nilai Buku','Keterangan'],
  'data' => [
  ['1', 'Printer Sharkpos', 2, 2025, 'Feb', '1 Tahun', '10.000.000', '1.000.000', '0', '10.000.000',
  '100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000',
  '1.200.000', '2.000.000', '8.000.000', 'Aktif']
  ],
  'subtotal' => ['-', 'Subtotal', '-', '-', '-', '250.000.000', '12.500', '-'],
  ];

  $table3 = [
  'title' => 'Table 3',
  'headers' => ['No', 'Mobil/Motor', 'Qty', 'Tahun', 'Bulan', 'Nilai Beli', 'Penyusutan', 'Catatan Tambahan'],
  'data' => [
  ['1', 'Canter 125PS', 1, 2023, 'Jan', '250.000.000', '12.500', 'Ready'],
  ],
  'subtotal' => ['-', 'Subtotal', '-', '-', '-', '250.000.000', '12.500', '-'],
  ];
  $table4 = [
  'title' => 'Table 4',
  'headers' => ['No', 'P', 'Qty', 'Tahun', 'Bulan', 'Periode', 'Nilai Perolehan', 'Akum. Peny', 'Mutasi Pembelian', 'Nilai Perolehan',
  'Penyusutan Januari', 'Penyusutan Februari', 'Penyusutan Maret', 'Penyusutan April', 'Penyusutan Mei', 'Penyusutan Juni', 'Penyusutan Juli',
  'Penyusutan Agustus','Penyusutan September','Penyusutan Oktober','Penyusutan November','Penyusutan Desember',
  'Total Penyusutan','Total Akumulasi Penyusutan','Nilai Buku','Keterangan'],
  'data' => [
  ['1', 'Printer Sharkpos', 2, 2025, 'Feb', '1 Tahun', '10.000.000', '1.000.000', '0', '10.000.000',
  '100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000','100.000',
  '1.200.000', '2.000.000', '8.000.000', 'Aktif']
  ],
  'subtotal' => ['-', 'Subtotal', '-', '-', '-', '250.000.000', '12.500', '-'],
  ];
  function normalizeTable(&$table) {
  $count = count($table['headers']);
  foreach ($table['data'] as &$row) {
  $row = array_pad($row, $count, '-');
  }
  $table['subtotal'] = array_pad($table['subtotal'], $count, '-');
  }

  normalizeTable($table1);
  normalizeTable($table2);
  normalizeTable($table3);
  @endphp

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
                <div class="table-responsive mt-2">

                  <x-dynamic-table :title="$table1['title']" :headers="$table1['headers']" :data="$table1['data']" :subtotal="$table1['subtotal']" />
                  <x-dynamic-table :title="$table2['title']" :headers="$table2['headers']" :data="$table2['data']" :subtotal="$table2['subtotal']" />
                  <x-dynamic-table :title="$table3['title']" :headers="$table3['headers']" :data="$table3['data']" :subtotal="$table3['subtotal']" />
                  <x-dynamic-table :title="$table4['title']" :headers="$table4['headers']" :data="$table4['data']" :subtotal="$table4['subtotal']" />

                </div>
              </div>
              <div class="tab-pane fade" id="navs-pills-top-profile" role="tabpanel">
                <div class="row mt-1">
                  <div class="col-md-2">
                    <button type="button" class=" btn-primary" onclick="showModalMasuk()"> 🔃 buat mutasi</button>
                  </div>
                </div>
                <div class="table-responsive mt-2">

                  <table id="kartuMasuk" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                      <tr>
                        <th>No</th>
                        <th>📅Tanggal</th>
                        <th> Kode barang</th>
                        <th> Nama Barang</th>
                        <th>Qty</th>
                        <th>🔢 Satuan</th>
                        <th>Rp/Unit</th>
                        <th>Total</th>
                      </tr>

                    </thead>
                    <tbody id="body-mutasi-masuk">
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="tab-pane fade" id="navs-pills-top-messages" role="tabpanel">
                <div class="col-md-2">
                  <button type="button" class=" btn-primary" onclick="showModalOut()"> 🔃 buat mutasi</button>
                </div>
                <div class="table-responsive mt-2">
                  <table id="kartuKeluar" class="table table-bordered table-striped table-hover align-middle">
                    <thead class="bg-white text-dark text-center">
                      <tr>
                        <th>No</th>
                        <th>📅Tanggal</th>
                        <th> Kode barang</th>
                        <th> Nama Barang</th>
                        <th>Qty</th>
                        <th>🔢 Satuan</th>
                        <th>Rp/Unit</th>
                        <th>Total</th>
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


  

</x-app-layout>