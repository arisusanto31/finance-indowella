<x-app-layout>
  <style>
    table.table {
      border-collapse: separate;
      border-spacing: 0;
      width: max-content;
      min-width: 1500px;
      table-layout: fixed;
      background-color: white;
    }

    table th, table td {
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
    .sticky-col-1, .sticky-col-2, .sticky-col-3, .sticky-col-4, .sticky-col-5 {
      position: sticky;
      background-color: white !important;
      z-index: 30;
      border-right: 1px solid #dee2e6;
    }

    .sticky-col-1 { left: 0px;   width: 60px;  min-width: 60px;  max-width: 60px;  }
    .sticky-col-2 { left: 60px;  width: 180px; min-width: 180px; max-width: 180px; }
    .sticky-col-3 { left: 240px; width: 80px;  min-width: 80px;  max-width: 80px;  }
    .sticky-col-4 { left: 320px; width: 100px; min-width: 100px; max-width: 100px; }
    .sticky-col-5 { left: 420px; width: 100px; min-width: 100px; max-width: 100px; }
  </style>

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

  <x-dynamic-table :title="$table1['title']" :headers="$table1['headers']" :data="$table1['data']" :subtotal="$table1['subtotal']" />
  <x-dynamic-table :title="$table2['title']" :headers="$table2['headers']" :data="$table2['data']" :subtotal="$table2['subtotal']" />
  <x-dynamic-table :title="$table3['title']" :headers="$table3['headers']" :data="$table3['data']" :subtotal="$table3['subtotal']" />
  <x-dynamic-table :title="$table4['title']" :headers="$table4['headers']" :data="$table4['data']" :subtotal="$table4['subtotal']" />
</x-app-layout>
